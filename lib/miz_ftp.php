<?php

class miz_ftp
{
    private const JSON_FILE = '/miz-data/ftptab.json';
    private const MAP_USER = 'www';
    private const USER_CONF_DIR = '/etc/vsftpd/virt_users.d';
    private const DB_FILE = '/etc/vsftpd/virt_users.db';

    public static function config_tpl(): array
    {
        return [
            'password' => '',
            'path' => '',
        ];
    }

    public static function get_all(): array
    {
        if (!is_file(self::JSON_FILE)) {
            return [];
        }

        $data = json_decode((string) file_get_contents(self::JSON_FILE), true);

        if (!is_array($data)) {
            return [];
        }

        $items = [];

        foreach ($data as $user => $item) {
            $items[(string) $user] = array_replace(
                self::config_tpl(),
                is_array($item) ? $item : []
            );
        }

        ksort($items, SORT_STRING);

        return $items;
    }

    public static function save_all(array $items): void
    {
        ksort($items, SORT_STRING);

        if (is_link(self::JSON_FILE)) {
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
            $written = file_put_contents(self::JSON_FILE, $json, LOCK_EX);
        } finally {
            umask($old_umask);
        }

        if ($written === false) {
            throw new RuntimeException('Không ghi được ' . self::JSON_FILE);
        }

        if (!chmod(self::JSON_FILE, 0600)) {
            throw new RuntimeException('Không chmod được ' . self::JSON_FILE);
        }
    }

    public static function sanitize_user(string $user): string
    {
        $user = trim($user);
        $user = preg_replace('/[^A-Za-z0-9._-]/', '_', $user);

        return trim((string) $user);
    }

    public static function normalize_path(string $path): string
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

    private static function run(string $cmd, ?string &$output = null): bool
    {
        $lines = [];
        exec($cmd . ' 2>&1', $lines, $exit_code);
        $output = implode("\n", $lines);

        return $exit_code === 0;
    }

    public static function apply(array $items): array
    {
        $tmp_source = null;

        try {
            $prepared = [];

            foreach ($items as $user => $item) {
                $user = (string) $user;
                $item = array_replace(
                    self::config_tpl(),
                    is_array($item) ? $item : []
                );

                $password = (string) $item['password'];
                $path = self::normalize_path((string) $item['path']);

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

            if (!is_dir(self::USER_CONF_DIR) && !mkdir(self::USER_CONF_DIR, 0755, true) && !is_dir(self::USER_CONF_DIR)) {
                throw new RuntimeException('Không tạo được ' . self::USER_CONF_DIR);
            }

            $files = scandir(self::USER_CONF_DIR);

            if ($files === false) {
                throw new RuntimeException('Không đọc được ' . self::USER_CONF_DIR);
            }

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $file_path = self::USER_CONF_DIR . '/' . $file;

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
                    . escapeshellarg(self::MAP_USER . ':' . self::MAP_USER)
                    . ' ' . escapeshellarg($dir);

                if (!self::run($cmd, $output)) {
                    throw new RuntimeException(
                        'Không chown được ' . $dir
                        . ($output !== '' ? "\n" . $output : '')
                    );
                }

                $conf = "local_root=" . $dir . "\n"
                    . "guest_username=" . self::MAP_USER . "\n"
                    . "virtual_use_local_privs=YES\n";

                $conf_file = self::USER_CONF_DIR . '/' . $user;

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

            if (is_file(self::DB_FILE) && !unlink(self::DB_FILE)) {
                throw new RuntimeException('Không xoá được DB cũ.');
            }

            $output = '';
            $cmd = 'db_load -T -t hash -f '
                . escapeshellarg($tmp_source)
                . ' ' . escapeshellarg(self::DB_FILE);

            $old_umask = umask(0077);

            try {
                $db_loaded = self::run($cmd, $output);
            } finally {
                umask($old_umask);
            }

            if (!$db_loaded) {
                throw new RuntimeException(
                    'db_load lỗi' . ($output !== '' ? "\n" . $output : '')
                );
            }

            clearstatcache(true, self::DB_FILE);

            if (!is_file(self::DB_FILE) || (int) filesize(self::DB_FILE) === 0) {
                throw new RuntimeException('DB không được tạo.');
            }

            if (!chmod(self::DB_FILE, 0600)) {
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

            if (!self::run('systemctl restart vsftpd', $output)) {
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
}
