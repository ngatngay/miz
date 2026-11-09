<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

const FTP_JSON_FILE = '/miz-data/ftptab.json';
const FTP_MAP_USER = 'www';
const FTP_USER_CONF_DIR = '/etc/vsftpd/virt_users.d';
const FTP_DB_FILE = '/etc/vsftpd/virt_users.db';

function ftp_config_tpl(): array
{
    return [
        'password' => '',
        'path' => '',
    ];
}

function ftp_get_all(): array
{
    if (!is_file(FTP_JSON_FILE)) {
        return [];
    }

    $data = json_decode((string) file_get_contents(FTP_JSON_FILE), true);

    if (!is_array($data)) {
        return [];
    }

    $items = [];

    foreach ($data as $user => $item) {
        $items[(string) $user] = array_replace(
            ftp_config_tpl(),
            is_array($item) ? $item : []
        );
    }

    ksort($items, SORT_STRING);

    return $items;
}

function ftp_save_all(array $items): void
{
    ksort($items, SORT_STRING);

    if (is_link(FTP_JSON_FILE)) {
        throw new RuntimeException('File JSON không hợp lệ.');
    }

    $json = json_encode(
        (object) $items,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        throw new RuntimeException('Không encode được JSON.');
    }

    $old_umask = umask(0077);

    try {
        $written = file_put_contents(FTP_JSON_FILE, $json, LOCK_EX);
    } finally {
        umask($old_umask);
    }

    if ($written === false) {
        throw new RuntimeException('Không ghi được ' . FTP_JSON_FILE);
    }

    if (!chmod(FTP_JSON_FILE, 0600)) {
        throw new RuntimeException('Không chmod được ' . FTP_JSON_FILE);
    }
}

function ftp_sanitize_user(string $user): string
{
    $user = trim($user);
    $user = preg_replace('/[^A-Za-z0-9._-]/', '_', $user);

    return trim((string) $user);
}

function ftp_normalize_path(string $path): string
{
    if (preg_match('/[\x00-\x1F\x7F]/', $path)) {
        throw new RuntimeException('Path chứa ký tự không hợp lệ.');
    }

    $path = trim($path);
    $path = trim($path, '/');
    $path = (string) preg_replace('#/+#', '/', $path);

    $parts = [];

    foreach (explode('/', $path) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }

        if ($part === '..') {
            throw new RuntimeException('Path không được chứa "..".');
        }

        $parts[] = $part;
    }

    return implode('/', $parts);
}

function ftp_run(string $cmd, ?string &$output = null): bool
{
    $lines = [];
    exec($cmd . ' 2>&1', $lines, $exit_code);
    $output = implode("\n", $lines);

    return $exit_code === 0;
}

function ftp_apply(array $items): array
{
    $tmp_source = null;

    try {
        $prepared = [];

        foreach ($items as $user => $item) {
            $user = (string) $user;
            $item = array_replace(
                ftp_config_tpl(),
                is_array($item) ? $item : []
            );

            $password = (string) $item['password'];
            $path = ftp_normalize_path((string) $item['path']);

            if (
                !preg_match('/^[A-Za-z0-9._-]+$/', $user)
                || $user === '.'
                || $user === '..'
                || !preg_match('/[A-Za-z0-9]/', $user)
            ) {
                throw new RuntimeException('User không hợp lệ: ' . $user);
            }

            if ($password === '' || preg_match('/[\x00\r\n]/', $password)) {
                throw new RuntimeException('Password của [' . $user . '] không hợp lệ.');
            }

            $prepared[$user] = [
                'password' => $password,
                'dir' => '/home/www/' . $path,
            ];
        }

        if (!is_dir('/etc/vsftpd') && !mkdir('/etc/vsftpd', 0755, true) && !is_dir('/etc/vsftpd')) {
            throw new RuntimeException('Không tạo được /etc/vsftpd');
        }

        if (!is_dir(FTP_USER_CONF_DIR) && !mkdir(FTP_USER_CONF_DIR, 0755, true) && !is_dir(FTP_USER_CONF_DIR)) {
            throw new RuntimeException('Không tạo được ' . FTP_USER_CONF_DIR);
        }

        $files = scandir(FTP_USER_CONF_DIR);

        if ($files === false) {
            throw new RuntimeException('Không đọc được ' . FTP_USER_CONF_DIR);
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $file_path = FTP_USER_CONF_DIR . '/' . $file;

            if (is_file($file_path) || is_link($file_path)) {
                if (!unlink($file_path)) {
                    throw new RuntimeException('Không xoá được ' . $file_path);
                }
            }
        }

        $virt = '';

        foreach ($prepared as $user => $data) {
            $dir = $data['dir'];

            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException('Không tạo được thư mục ' . $dir);
            }

            $output = '';
            $cmd = 'chown -R '
                . escapeshellarg(FTP_MAP_USER . ':' . FTP_MAP_USER)
                . ' ' . escapeshellarg($dir);

            if (!ftp_run($cmd, $output)) {
                throw new RuntimeException(
                    'Không chown được ' . $dir
                    . ($output !== '' ? "\n" . $output : '')
                );
            }

            $conf = "local_root=" . $dir . "\n"
                . "guest_username=" . FTP_MAP_USER . "\n"
                . "virtual_use_local_privs=YES\n";

            $conf_file = FTP_USER_CONF_DIR . '/' . $user;

            if (file_put_contents($conf_file, $conf) === false) {
                throw new RuntimeException('Không ghi được ' . $conf_file);
            }

            chmod($conf_file, 0644);

            $virt .= $user . "\n" . $data['password'] . "\n";
        }

        $tmp_source = @tempnam('/miz-data', 'ftptab.json-tmpdb-');

        if ($tmp_source === false || strpos($tmp_source, '/miz-data/') !== 0) {
            throw new RuntimeException('Không tạo được file tạm DB.');
        }

        if (!chmod($tmp_source, 0600)) {
            throw new RuntimeException('Không chmod được file tạm DB.');
        }

        if (file_put_contents($tmp_source, $virt, LOCK_EX) === false) {
            throw new RuntimeException('Không ghi được file tạm DB.');
        }

        if (is_file(FTP_DB_FILE) && !unlink(FTP_DB_FILE)) {
            throw new RuntimeException('Không xoá được DB cũ.');
        }

        $output = '';
        $cmd = 'db_load -T -t hash -f '
            . escapeshellarg($tmp_source)
            . ' ' . escapeshellarg(FTP_DB_FILE);

        $old_umask = umask(0077);

        try {
            $db_loaded = ftp_run($cmd, $output);
        } finally {
            umask($old_umask);
        }

        if (!$db_loaded) {
            throw new RuntimeException(
                'db_load lỗi' . ($output !== '' ? "\n" . $output : '')
            );
        }

        clearstatcache(true, FTP_DB_FILE);

        if (!is_file(FTP_DB_FILE) || (int) filesize(FTP_DB_FILE) === 0) {
            throw new RuntimeException('DB không được tạo.');
        }

        if (!chmod(FTP_DB_FILE, 0600)) {
            throw new RuntimeException('Không chmod được DB.');
        }

        $pam = "auth required pam_userdb.so db=/etc/vsftpd/virt_users\n"
            . "account required pam_userdb.so db=/etc/vsftpd/virt_users\n";

        if (file_put_contents('/etc/pam.d/vsftpd', $pam) === false) {
            throw new RuntimeException('Không ghi được /etc/pam.d/vsftpd');
        }

        chmod('/etc/pam.d/vsftpd', 0644);

        if (!copy('/miz/tpl/vsftpd.conf', '/etc/vsftpd.conf')) {
            throw new RuntimeException('Không copy được /etc/vsftpd.conf');
        }

        chmod('/etc/vsftpd.conf', 0644);

        $output = '';

        if (!ftp_run('systemctl restart vsftpd', $output)) {
            throw new RuntimeException(
                'Restart vsftpd lỗi' . ($output !== '' ? "\n" . $output : '')
            );
        }

        return [true, ''];
    } catch (Throwable $e) {
        return [false, $e->getMessage()];
    } finally {
        if (is_string($tmp_source) && is_file($tmp_source)) {
            @unlink($tmp_source);
        }
    }
}

function ftptab_fields(array $item, string $user = '', bool $is_edit = false): void
{
    $item = array_replace(ftp_config_tpl(), $item);

    echo '<div class="row g-2 mb-2">';
    echo '<div class="col-md-4">';
    echo '<label class="form-label">user</label>';
    echo '<input class="form-control form-control-sm" type="text" name="user" value="' . e($user) . '" placeholder="site1" required>';
    echo '</div>';
    echo '<div class="col-md-8">';
    echo '<label class="form-label">password</label>';
    echo '<input class="form-control form-control-sm font-monospace" type="password" name="password" value="" placeholder="' . ($is_edit ? 'Để trống nếu không đổi' : 'password...') . '"' . ($is_edit ? '' : ' required') . '>';
    echo '</div>';
    echo '</div>';

    echo '<div class="mb-2">';
    echo '<label class="form-label">path</label>';
    echo '<div class="input-group input-group-sm">';
    echo '<span class="input-group-text">/home/www/</span>';
    echo '<input class="form-control font-monospace" type="text" name="path" value="' . e($item['path']) . '" placeholder="site1 (có thể trống)">';
    echo '</div>';
    echo '</div>';
}

$site_title = 'ftp';
$items = ftp_get_all();
$errors = [];
$add_form = ['user' => '', 'path' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $old_user = (string) ($_POST['old_user'] ?? '');
    $user = '';
    $password = '';
    $path = '';

    try {
        if ($action === 'delete') {
            if (!isset($items[$old_user])) {
                throw new RuntimeException('Tài khoản FTP không tồn tại.');
            }

            unset($items[$old_user]);
            ftp_save_all($items);

            [$ok, $output] = ftp_apply($items);

            if (!$ok) {
                throw new RuntimeException($output !== '' ? $output : 'Áp dụng cấu hình thất bại.');
            }

            header('Location: /ftptab.php?done=delete');
            exit;
        }

        $user = ftp_sanitize_user((string) ($_POST['user'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $path = (string) ($_POST['path'] ?? '');
        $path = ftp_normalize_path($path);

        if (
            $user === ''
            || $user === '.'
            || $user === '..'
            || !preg_match('/^[A-Za-z0-9._-]+$/', $user)
            || !preg_match('/[A-Za-z0-9]/', $user)
        ) {
            throw new RuntimeException('User không hợp lệ.');
        }

        if ($action === 'add') {
            if (isset($items[$user])) {
                throw new RuntimeException('User đã tồn tại.');
            }

            if ($password === '' || preg_match('/[\x00\r\n]/', $password)) {
                throw new RuntimeException('Password không hợp lệ.');
            }

            $items[$user] = [
                'password' => $password,
                'path' => $path,
            ];
        } elseif ($action === 'edit') {
            if (!isset($items[$old_user])) {
                throw new RuntimeException('Tài khoản FTP không tồn tại.');
            }

            if ($user !== $old_user && isset($items[$user])) {
                throw new RuntimeException('User đã tồn tại.');
            }

            if ($password === '') {
                $password = (string) $items[$old_user]['password'];
            }

            if (preg_match('/[\x00\r\n]/', $password)) {
                throw new RuntimeException('Password không hợp lệ.');
            }

            unset($items[$old_user]);

            $items[$user] = [
                'password' => $password,
                'path' => $path,
            ];
        } else {
            throw new RuntimeException('Hành động không hợp lệ.');
        }

        ftp_save_all($items);

        [$ok, $output] = ftp_apply($items);

        if (!$ok) {
            throw new RuntimeException($output !== '' ? $output : 'Áp dụng cấu hình thất bại.');
        }

        header('Location: /ftptab.php?done=' . urlencode($action));
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
        $items = ftp_get_all();

        if ($action === 'add') {
            $add_form = [
                'user' => $user,
                'path' => $path,
            ];
        }
    }
}

require SITE_HEADER;

$done = $_GET['done'] ?? '';

if ($done === 'add') {
    t_success('Thêm thành công!');
} elseif ($done === 'edit') {
    t_success('Sửa thành công!');
} elseif ($done === 'delete') {
    t_success('Xoá thành công!');
}

if (!empty($errors)) {
    echo '<div class="alert alert-danger">';

    foreach ($errors as $error) {
        echo '<pre class="mb-0" style="white-space: pre-wrap;">' . e($error) . '</pre>';
    }

    echo '</div>';
}

echo '<h1 class="h3 mb-3">FTP</h1>';

echo '<div class="card shadow-sm mb-3 small">';
echo '<div class="card-body">';
echo '<form method="post">';
$add_item = ['path' => $add_form['path']];
ftptab_fields($add_item, (string) $add_form['user'], false);
echo '<button type="submit" class="btn btn-primary btn-sm" name="action" value="add">Thêm</button>';
echo '</form>';
echo '</div>';
echo '</div>';

foreach ($items as $user => $item) {
    echo '<div class="card shadow-sm mb-3 small">';
    echo '<div class="card-body">';
    echo '<form method="post">';
    echo '<input type="hidden" name="old_user" value="' . e($user) . '">';
    ftptab_fields($item, (string) $user, true);
    echo '<div class="d-flex gap-2">';
    echo '<button type="submit" class="btn btn-warning btn-sm" name="action" value="edit">Sửa</button>';
    echo '<button type="submit" class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm(\'Xoá tài khoản FTP này? Dữ liệu trên đĩa không bị xoá.\')">Xoá</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}

require SITE_FOOTER;
