<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

function ftptab_fields(array $item, string $user = '', bool $is_edit = false): void
{
    $item = array_replace(miz_ftp::config_tpl(), $item);

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

$old_user = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (string) ($_POST['old_user'] ?? $_GET['user'] ?? '')
    : (string) ($_GET['user'] ?? '');
$is_edit = $old_user !== '';
$items = miz_ftp::get_all();
$item = $is_edit ? ($items[$old_user] ?? null) : [];
$errors = [];
$form_data = is_array($item) ? array_replace(miz_ftp::config_tpl(), $item) : miz_ftp::config_tpl();
$form_user = $is_edit ? $old_user : '';

if ($is_edit && !is_array($item)) {
    $errors[] = 'Tài khoản FTP không tồn tại.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = '';
    $password = (string) ($_POST['password'] ?? '');
    $path = '';
    $saved = false;

    try {
        if ($is_edit && !array_key_exists($old_user, $items)) {
            throw new RuntimeException('Tài khoản FTP không tồn tại.');
        }

        $user = miz_ftp::sanitize_user((string) ($_POST['user'] ?? ''));
        $form_user = $user;
        $path = (string) ($_POST['path'] ?? '');
        $form_data = ['password' => '', 'path' => $path];
        $path = miz_ftp::normalize_path($path);
        $form_data['path'] = $path;

        if (
            $user === ''
            || $user === '.'
            || $user === '..'
            || !preg_match('/^[A-Za-z0-9._-]+$/', $user)
            || !preg_match('/[A-Za-z0-9]/', $user)
        ) {
            throw new RuntimeException('User không hợp lệ.');
        }

        if ($is_edit) {
            if ($user !== $old_user && isset($items[$user])) {
                throw new RuntimeException('User đã tồn tại.');
            }

            if ($password === '') {
                $password = (string) $items[$old_user]['password'];
            }
        } else {
            if (isset($items[$user])) {
                throw new RuntimeException('User đã tồn tại.');
            }

            if ($password === '') {
                throw new RuntimeException('Password không hợp lệ.');
            }
        }

        if (preg_match('/[\x00\r\n]/', $password)) {
            throw new RuntimeException('Password không hợp lệ.');
        }

        if ($is_edit) {
            unset($items[$old_user]);
        }

        $items[$user] = [
            'password' => $password,
            'path' => $path,
        ];

        $done_action = $is_edit ? 'edit' : 'add';
        miz_ftp::save_all($items);
        $saved = true;

        // Nếu apply lỗi sau khi JSON đã lưu, giữ form ở chế độ sửa để có thể thử lại.
        $old_user = $user;
        $is_edit = true;
        $form_data = $items[$user];

        [$ok, $output] = miz_ftp::apply($items);

        if (!$ok) {
            throw new RuntimeException($output !== '' ? $output : 'Áp dụng cấu hình thất bại.');
        }

        header('Location: /ftptab.php?done=' . $done_action);
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
        $items = miz_ftp::get_all();

        if ($user !== '') {
            $form_user = $user;
            $form_data = ['password' => '', 'path' => $path];

            if ($saved && isset($items[$user])) {
                $old_user = $user;
                $is_edit = true;
                $form_data = $items[$user];
            }
        }
    }
}

$site_title = $is_edit ? 'Sửa tài khoản FTP' : 'Thêm tài khoản FTP';
require SITE_HEADER;
?>

<h1 class="h4 mb-3"><?= e($site_title) ?></h1>

<?php if ($errors) { ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $error) { ?>
    <div><?= e($error) ?></div>
    <?php } ?>
</div>
<?php } ?>

<?php if (!$is_edit || is_array($item) || isset($items[$old_user])) { ?>
<form method="post">
    <?php if ($is_edit) { ?>
    <input type="hidden" name="old_user" value="<?= e($old_user) ?>">
    <?php } ?>

    <div class="card shadow-sm mb-3 small">
        <div class="card-body">
            <?php ftptab_fields($form_data, $form_user, $is_edit); ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $is_edit ? 'Lưu' : 'Thêm' ?></button>
                <a class="btn btn-outline-secondary btn-sm" href="/ftptab.php">Hủy</a>
            </div>
        </div>
    </div>
</form>
<?php } else { ?>
<a class="btn btn-outline-secondary btn-sm" href="/ftptab.php">Quay lại danh sách</a>
<?php } ?>

<?php require SITE_FOOTER; ?>
