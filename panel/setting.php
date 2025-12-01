<?php

define('ACCESS', true);

require __DIR__ . '/_init.php';

$site_title = 'cài đặt';

require '_header.php';

?>

<div class="title"><?= e($site_title) ?></div>

<div class="content">
    <div class="alert alert-warning">để trống sẽ dùng mặc định</div>

    <?php if (!empty($_POST)) { t_success('OK'); } ?>

    <form class="layui-form" method="post">
        <div class="layui-form-item">
            <label>apache custom config</label>
            <textarea name="apache_conf" class="layui-textarea"><?= e($_POST['apache_conf'] ?? setting_get('apache_conf')) ?></textarea>
            <!-- <div class="layui-form-mid layui-word-aux">Ngăn cách các domain bằng dấu cách.</div> -->
        </div>

        <div class="layui-form-item">
            <label>mariadb custom config</label>
            <textarea name="mariadb_conf" class="layui-textarea"><?= e($_POST['apache_conf'] ?? setting_get('apache_conf')) ?></textarea>
        </div>

        <div class="layui-form-item">
            <button type="submit" class="layui-btn layui-btn-normal">Lưu</button>
            <a class="layui-btn layui-btn-primary" href="/">Hủy</a>
        </div>
    </form>
</div>

<?php require '_footer.php'; ?>