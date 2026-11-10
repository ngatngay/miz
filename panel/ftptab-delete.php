<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$user = (string) ($_GET['user'] ?? $_POST['user'] ?? '');
$items = miz_ftp::get_all();
$item = $user !== '' ? ($items[$user] ?? null) : null;
$errors = [];
$deleted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($item)) {
    try {
        unset($items[$user]);
        miz_ftp::save_all($items);
        $deleted = true;

        [$ok, $output] = miz_ftp::apply($items);

        if (!$ok) {
            throw new RuntimeException($output !== '' ? $output : 'Áp dụng cấu hình thất bại.');
        }

        header('Location: /ftptab.php?done=delete');
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
        $items = miz_ftp::get_all();
        $item = $items[$user] ?? null;
    }
}

$site_title = 'Xóa tài khoản FTP';
require SITE_HEADER;
?>

<h1 class="h4 mb-3">Xóa tài khoản FTP</h1>

<?php if ($errors) { ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $error) { ?>
    <div><?= e($error) ?></div>
    <?php } ?>
</div>
<?php } ?>

<?php if ($deleted && !is_array($item)) { ?>
<div class="alert alert-warning">Tài khoản đã bị xóa khỏi danh sách cấu hình nhưng cấu hình hệ thống chưa áp dụng thành công.</div>
<a class="btn btn-outline-secondary btn-sm" href="/ftptab.php">Quay lại danh sách</a>
<?php } elseif (!is_array($item)) { ?>
<div class="alert alert-danger">Tài khoản FTP không tồn tại.</div>
<a class="btn btn-outline-secondary btn-sm" href="/ftptab.php">Quay lại danh sách</a>
<?php } else { ?>
<div class="card shadow-sm mb-3 small">
    <div class="card-body">
        <div class="mb-2"><span class="text-body-secondary">user:</span> <?= e($user) ?></div>
        <div class="mb-2"><span class="text-body-secondary">path:</span> <span class="font-monospace">/home/www/<?= e($item['path']) ?></span></div>
        <div class="text-body-secondary">Xóa tài khoản sẽ không xóa dữ liệu trên đĩa.</div>
    </div>
</div>

<form method="post" class="d-flex gap-2">
    <input type="hidden" name="user" value="<?= e($user) ?>">
    <button type="submit" class="btn btn-danger btn-sm">Xóa tài khoản FTP</button>
    <a class="btn btn-outline-secondary btn-sm" href="/ftptab.php">Hủy</a>
</form>
<?php } ?>

<?php require SITE_FOOTER; ?>
