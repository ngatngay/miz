<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'crontab';
$items = miz_cron::get_all();
$done = (string) ($_GET['done'] ?? '');

require SITE_HEADER;

if ($done === 'add') {
    t_success('Thêm thành công!');
} elseif ($done === 'edit') {
    t_success('Sửa thành công!');
} elseif ($done === 'delete') {
    t_success('Xoá thành công!');
}

echo '<div class="d-flex align-items-center justify-content-between mb-3">';
echo '<h1 class="h3 mb-0">Crontab</h1>';
echo '<a class="btn btn-primary btn-sm" href="/crontab-edit.php">Thêm cron</a>';
echo '</div>';

if (!$items) {
    echo '<div class="alert alert-info">Chưa có cron nào.</div>';
}

foreach ($items as $id => $item) {
    $id = (string) $id;
    $item = array_replace(miz_cron::config_tpl(), is_array($item) ? $item : []);

    echo '<div class="card shadow-sm mb-3 small">';
    echo '<div class="card-body">';
    echo '<div class="row g-2 mb-2">';
    echo '<div class="col-md-3"><div class="text-body-secondary">user</div><div>' . e($item['user']) . '</div></div>';
    echo '<div class="col-md-9"><div class="text-body-secondary">expression</div><div class="font-monospace">' . e($item['expression']) . '</div></div>';
    echo '</div>';
    echo '<div class="mb-2"><div class="text-body-secondary">cmd</div><div class="font-monospace text-break">' . e($item['cmd']) . '</div></div>';

    if ($item['desc'] !== '') {
        echo '<div class="mb-3"><div class="text-body-secondary">desc</div><div>' . nl2br(e($item['desc'])) . '</div></div>';
    }

    echo '<div class="d-flex gap-2">';
    echo '<a class="btn btn-warning btn-sm" href="/crontab-edit.php?id=' . rawurlencode($id) . '">Sửa</a>';
    echo '<a class="btn btn-danger btn-sm" href="/crontab-delete.php?id=' . rawurlencode($id) . '">Xóa</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

require SITE_FOOTER;
