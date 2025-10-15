<?php

define('ACCESS', true);

require __DIR__ . '/../miz.php';

$domains = domains_load();
$id = (string) ($_GET['id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($domains[$id]);
    domains_dump($domains);

    header('Location: /');
}

$site_title = 'xoá domain';

require 'header.php';

?>

<div class="title">Xoá domain</div>

<div class="content">
    <?php if (!domain_exists($id)) { ?>
        <div class="alert alert-danger">
            domain không tồn tại
        </div>
    <?php } else { ?>
        <pre class="layui-code layui-code-theme-dark"><?php var_export($domains[$id]) ?></pre>
        <br>

        <form class="layui-form" method="post">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button type="submit" class="layui-btn layui-btn-danger">Xoá</button>
                    <a class="layui-btn layui-btn-primary" href="index.php">Hủy</a>
                </div>
            </div>
        </form>
    <?php } ?>
</div>

<?php

require 'footer.php';
