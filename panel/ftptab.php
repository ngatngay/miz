<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'ftp';
$items = miz_ftp::get_all();
$done = (string) ($_GET['done'] ?? '');

require SITE_HEADER;

if ($done === 'add') {
    t_success('Thêm thành công!');
} elseif ($done === 'edit') {
    t_success('Sửa thành công!');
} elseif ($done === 'delete') {
    t_success('Xoá thành công!');
}
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">FTP</h1>
    <a class="btn btn-primary btn-sm" href="/ftptab-edit.php">Thêm tài khoản FTP</a>
</div>

<?php if (!$items) { ?>
<div class="alert alert-info">Chưa có tài khoản FTP nào.</div>
<?php } ?>

<?php foreach ($items as $user => $item) {
    $item = array_replace(miz_ftp::config_tpl(), is_array($item) ? $item : []);
?>
<div class="card shadow-sm mb-3 small">
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="text-body-secondary">user</div>
                <div class="font-monospace"><?= e((string) $user) ?></div>
            </div>
            <div class="col-md-8">
                <div class="text-body-secondary">path</div>
                <div class="font-monospace text-break">/home/www/<?= e((string) $item['path']) ?></div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-warning btn-sm" href="/ftptab-edit.php?user=<?= rawurlencode((string) $user) ?>">Sửa</a>
            <a class="btn btn-danger btn-sm" href="/ftptab-delete.php?user=<?= rawurlencode((string) $user) ?>">Xóa</a>
        </div>
    </div>
</div>
<?php } ?>

<?php require SITE_FOOTER; ?>
