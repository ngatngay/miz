<?php

define('ACCESS', true);

require __DIR__ . '/../miz.php';

$site_title = 'domain list';

require 'header.php';

?>

<?php $domains = domains_load(); ?>

<div class="title">Domains (<?= count($domains) ?>)</div>

<div class="content">
    &bull; sau khi chỉnh sửa, mở ssh chạy lệnh: <b><code>miz update_web</code></b>
    <hr>
    &bull; <a href="domain_edit.php">thêm domain</a>
</div>

<div class="content">
<?php
    foreach ($domains as $domain_id => $domain_config) {
        $domain_list = array_filter(explode(' ', trim((string) ($domain_config['domains'] ?? ''))));
        ?>
        <blockquote id="domain-<?= $domain_id ?>" class="layui-elem-quote layui-quote-nm layui-font-12">    
            <div class="layui-btn-container">
                <?php foreach ($domain_list as $domain_name) {
                    $href = 'https://' . (strpos($domain_name, '*.') === 0 ? substr($domain_name, 2) : $domain_name);
                    ?>
                    <a class="layui-btn layui-btn-xs layui-btn-primary layui-border" href="<?= e($href) ?>" target="_blank">
                        <?= e($domain_name) ?>
                    </a>
                    <?php
                } ?>
            </div>
            <span class="layui-btn layui-btn-xs <?= $domain_config['status'] === 'on' ? '' : 'layui-bg-red' ?>"><?= $domain_config['status'] ?></span>
            <span class="layui-btn layui-btn-xs layui-btn-primary layui-border-green"><?= $domain_config['mode'] ?></span>
            <br><span><i class="layui-icon layui-icon-tips-fill layui-font-12"></i> php <?= e((string) ($domain_config['php'] ?? '')) ?></span>
            <br><span><i class="layui-icon layui-icon-folder layui-font-12"></i> <?= e((string) ($domain_config['dir'] ?? '')) ?></span>
            <hr>
            <div class="layui-btn-container">
                <!--
                <a class="layui-btn layui-btn-sm layui-btn-primary layui-border-blue" href="">bật</a>
                <a class="layui-btn layui-btn-sm layui-btn-primary layui-border-red" href="">tắt</a>
                -->
                <a class="layui-btn layui-btn-sm layui-bg-orange" href="/domain_edit.php?id=<?= $domain_id ?>">sửa</a>
                <a class="layui-btn layui-btn-sm layui-bg-red" href="/domain_delete.php?id=<?= $domain_id ?>">xoá</a>
            </div>
        </blockquote>
        <?php
    }
    ?>
</div>

<script>
var id = location.hash.substring(1);
if (id) $('#' + id).css('border-color', '#ff5722');
</script>

<?php require 'footer.php'; ?>
