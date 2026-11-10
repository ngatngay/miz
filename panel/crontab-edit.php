<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

function crontab_form_fields(array $item): void
{
    $item = array_replace(miz_cron::config_tpl(), $item);

    echo '<div class="row g-2 mb-2">';
    echo '<div class="col-md-3">';
    echo '<label class="form-label">user</label>';
    echo '<input class="form-control form-control-sm" type="text" name="user" value="' . e($item['user']) . '" placeholder="www" required>';
    echo '</div>';
    echo '<div class="col-md-9">';
    echo '<label class="form-label">expression</label>';
    echo '<input class="form-control form-control-sm font-monospace" type="text" name="expression" value="' . e($item['expression']) . '" placeholder="* * * * *" required>';
    echo '</div>';
    echo '</div>';

    echo '<div class="mb-2">';
    echo '<label class="form-label">cmd</label>';
    echo '<input class="form-control form-control-sm font-monospace" type="text" name="cmd" value="' . e($item['cmd']) . '" placeholder="command..." required>';
    echo '</div>';

    echo '<div class="mb-2">';
    echo '<label class="form-label">desc</label>';
    echo '<textarea class="form-control form-control-sm font-monospace" name="desc" rows="3" placeholder="mô tả...">' . e($item['desc']) . '</textarea>';
    echo '</div>';
}

$id = (string) ($_GET['id'] ?? '');
$is_edit = $id !== '';
$items = miz_cron::get_all();
$item = $is_edit ? ($items[$id] ?? null) : [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
    $is_edit = $id !== '';
    $items = miz_cron::get_all();
    $item = $is_edit ? ($items[$id] ?? null) : [];

    if ($is_edit && !array_key_exists($id, $items)) {
        $errors[] = 'Cron không tồn tại.';
    } else {
        $data = array_replace(miz_cron::config_tpl(), $_POST);
        unset($data['action'], $data['id']);

        if ($is_edit) {
            miz_cron::update($id, $data);
        } else {
            miz_cron::add($data);
        }

        header('Location: /crontab.php?done=' . ($is_edit ? 'edit' : 'add'));
        exit;
    }
}

if ($is_edit && !array_key_exists($id, $items)) {
    $item = null;
    if (!$errors) {
        $errors[] = 'Cron không tồn tại.';
    }
}

if (is_array($item)) {
    $form_data = array_replace(miz_cron::config_tpl(), $item, $_POST);
} else {
    $form_data = array_replace(miz_cron::config_tpl(), $_POST);
}

$site_title = $is_edit ? 'Sửa cron' : 'Thêm cron';
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

<?php if (!$is_edit || is_array($item)) { ?>
<form method="post">
    <?php if ($is_edit) { ?>
    <input type="hidden" name="id" value="<?= e($id) ?>">
    <?php } ?>

    <div class="card shadow-sm mb-3 small">
        <div class="card-body">
            <?php crontab_form_fields($form_data); ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $is_edit ? 'Lưu' : 'Thêm' ?></button>
                <a class="btn btn-outline-secondary btn-sm" href="/crontab.php">Hủy</a>
            </div>
        </div>
    </div>
</form>
<?php } else { ?>
<a class="btn btn-outline-secondary btn-sm" href="/crontab.php">Quay lại danh sách</a>
<?php } ?>

<?php require SITE_FOOTER; ?>
