<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'crontab';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $data = array_replace(miz_cron::config_tpl(), $_POST);
        unset($data['action']);

        miz_cron::add($data);
    } elseif ($action === 'edit') {
        $data = array_replace(miz_cron::config_tpl(), $_POST);
        unset($data['action'], $data['id']);

        miz_cron::update($_POST['id'], $data);
    } elseif ($action === 'delete') {
        miz_cron::delete($_POST['id']);
    }

    header('Location: /crontab.php?done=' . $action);
    exit;
}

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

$items = miz_cron::get_all();

require SITE_HEADER;

$done = $_GET['done'] ?? '';

if ($done === 'add') {
    t_success('Thêm thành công!');
} elseif ($done === 'edit') {
    t_success('Sửa thành công!');
} elseif ($done === 'delete') {
    t_success('Xoá thành công!');
}

echo '<h1 class="h3 mb-3">Crontab</h1>';

echo '<div class="card shadow-sm mb-3 small">';
echo '<div class="card-body">';
echo '<form method="post">';
crontab_form_fields([]);
echo '<button type="submit" class="btn btn-primary btn-sm" name="action" value="add">Thêm</button>';
echo '</form>';
echo '</div>';
echo '</div>';

foreach ($items as $id => $item) {
    echo '<div class="card shadow-sm mb-3 small">';
    echo '<div class="card-body">';
    echo '<form method="post">';
    echo '<input type="hidden" name="id" value="' . e($id) . '">';
    crontab_form_fields($item);
    echo '<div class="d-flex gap-2">';
    echo '<button type="submit" class="btn btn-warning btn-sm" name="action" value="edit" onclick="return confirm(\'Sửa cron này? KHÔNG ẢNH HƯỞNG CRON KHÁC ĐÂU NHÉ!!!\')">Sửa</button>';
    echo '<button type="submit" class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm(\'Xoá cron này?\')">Xoá</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}

require SITE_FOOTER;
