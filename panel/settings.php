<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'settings';

foreach ($_POST as $k => $v) {
    $_POST[$k] = is_string($v) ? trim($v) : $v;
    
    if (empty($_POST[$k])) {
        unset($_POST[$k]);
    }
}

$settings = array_replace(
    miz_setting::get_all(),
    $_POST
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    miz_setting::save($settings);
}

require SITE_HEADER;

?>

<h1 class="h4 mb-3">Settings</h1>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
<div class="alert alert-success">
    Lưu thành công!
</div>
<?php } ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">http_keepalive</label>
        <input class="form-control" type="number" name="http_keepalive" value="<?= e($settings['http_keepalive']) ?>">
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a class="btn btn-outline-secondary" href="/">Hủy</a>
    </div>
</form>

<?php require SITE_FOOTER; ?>
