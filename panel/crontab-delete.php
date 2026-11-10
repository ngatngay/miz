<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$items = miz_cron::get_all();
$item = $id !== '' ? ($items[$id] ?? null) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_array($item)) {
        $error = 'Cron không tồn tại.';
    } else {
        miz_cron::delete($id);
        header('Location: /crontab.php?done=delete');
        exit;
    }
}

$site_title = 'Xóa cron';
require SITE_HEADER;
?>

<h1 class="h4 mb-3">Xóa cron</h1>

<?php if (!is_array($item)) { ?>
<div class="alert alert-danger"><?= e($error ?? 'Cron không tồn tại.') ?></div>
<a class="btn btn-outline-secondary btn-sm" href="/crontab.php">Quay lại danh sách</a>
<?php } else { ?>
<div class="card shadow-sm mb-3 small">
    <div class="card-body">
        <div class="mb-2"><span class="text-body-secondary">user:</span> <?= e($item['user'] ?? '') ?></div>
        <div class="mb-2"><span class="text-body-secondary">expression:</span> <span class="font-monospace"><?= e($item['expression'] ?? '') ?></span></div>
        <div class="mb-2"><span class="text-body-secondary">cmd:</span> <span class="font-monospace text-break"><?= e($item['cmd'] ?? '') ?></span></div>
        <?php if (!empty($item['desc'])) { ?>
        <div class="mb-2"><span class="text-body-secondary">desc:</span><br><?= nl2br(e($item['desc'])) ?></div>
        <?php } ?>
    </div>
</div>

<form method="post" class="d-flex gap-2">
    <input type="hidden" name="id" value="<?= e($id) ?>">
    <button type="submit" class="btn btn-danger btn-sm">Xóa cron</button>
    <a class="btn btn-outline-secondary btn-sm" href="/crontab.php">Hủy</a>
</form>
<?php } ?>

<?php require SITE_FOOTER; ?>
