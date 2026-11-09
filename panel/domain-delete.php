<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$id = (string) ($_GET['id'] ?? '');
$domain = miz_domain::get($id);
$domain_exists = !empty($domain);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($domain_exists) {
        miz_domain::unset($id);
        header('Location: /');
        exit;
    }
}

$site_title = 'xoá domain';

require SITE_HEADER;

?>

<h1 class="h4 mb-3">Xóa domain</h1>

<?php if (!$domain_exists) { ?>
    <div class="alert alert-danger">Domain không tồn tại</div>
<?php } else { ?>
    <pre class="code-custom"><?php var_export($domain) ?></pre>

    <form method="post" class="mt-3">
        <button type="submit" class="btn btn-danger">Xóa</button>
        <a class="btn btn-outline-secondary" href="/domain-list.php">Hủy</a>
    </form>
<?php } ?>

<?php require SITE_FOOTER; ?>
