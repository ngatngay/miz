<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

// pre
foreach ($_POST as $key => $value) {
    $_POST[$key] = is_string($value) ? trim($value) : $value;
}

// run
$id = (string) ($_GET['id'] ?? '');   
$show_success = !empty($_GET['success']);
$success_act = (string) ($_GET['act'] ?? '');
$domain = miz_domain::get($id);

if ($id && empty($domain['domains'])) {
    require SITE_HEADER;
    echo '<div class="alert alert-danger">Domain <b>' . e($id) . '</b> không tồn tại</div>';
    require SITE_FOOTER;
    exit;
}

$is_edit_mode = !empty($id);
$curr_domains = miz_domain::parse_domains($domain['domains']);
$editing_domain = $is_edit_mode ? $domain : [];

$modes = [
    'php',
    'static',
    'proxy'
];

$php_versions = [
    '0' => 'Tắt',
    '5.6' => 'PHP 5.6',
    '7.4' => 'PHP 7.4',
    '8.0' => 'PHP 8.0',
    '8.1' => 'PHP 8.1',
    '8.2' => 'PHP 8.2',
    '8.3' => 'PHP 8.3',
    '8.4' => 'PHP 8.4',
    '8.5' => 'PHP 8.5',
];

$editing_data = $editing_domain;
$forms = array_replace(miz_domain::config_tpl(), $domain, $_POST);
$forms['domains'] = preg_replace('/\s+/', ' ', $forms['domains']);
$forms['mode_php'] = $forms['mode_php'] ?: '8.2';
$forms['dir'] = trim($forms['dir'], '/');
$forms['dir_run'] = trim($forms['dir_run'], '/');

$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain_names = miz_domain::parse_domains($forms['domains']);
    if (empty($domain_names)) {
        $errors[] = 'Vui lòng nhập ít nhất một domain.';
    }

    if (!array_key_exists($forms['mode_php'], $php_versions)) {
        $errors[] = 'Phiên bản PHP không hợp lệ.';
    }

    if (!preg_match('/^[a-zA-Z0-9.* -]+$/', $forms['domains'])) {
        $errors[] = 'Domain chứa ký tự không hợp lệ.';
    }

    if (empty($errors)) {
        $all_domain_names = miz_domain::get_list();
        $all_domain_dup = [];

        // loại domain đã có của id hiện tại
        if ($is_edit_mode) {
            $all_domain_names = array_diff($all_domain_names, $curr_domains);
        }

        foreach ($domain_names as $domain_one) {
            foreach ($all_domain_names as $domain_two) {
                if (strtolower($domain_one) == strtolower($domain_two)) {
                    $all_domain_dup[] = $domain_one;
                }
            }
        }

        if (!empty($all_domain_dup)) {
            $errors[] = 'Các domain đã tồn tại: ' . implode(', ', $all_domain_dup);
        }
    }

    if (empty($errors)) {
        $target_id = miz_domain::parse_id($forms['domains']);

        if (empty($target_id)) {
            $errors[] = 'Không xác định được domain chính.';
        } else {
            miz_domain::save($target_id, $forms);
            if ($is_edit_mode && $target_id !== $id) {
                miz_domain::unset($id);
            }
            header('Location: domain_edit.php?id=' . urlencode($target_id) . '&success=1&act=' . ($is_edit_mode ? 'edit' : 'add'));
            exit;
        }
    }
}

$site_title = $is_edit_mode ? 'Cập nhật domain' : 'Thêm domain';
$current_storage_id = $id !== '' ? $id : miz_domain::parse_id((string) ($forms['domains'] ?? ''));
$conf_path = '/miz_data/domains/' . $current_storage_id . '/tmp';

require SITE_HEADER;

?>

<h1 class="h4 mb-3"><?= e($site_title) ?></h1>

<?php if (!empty($errors)) { ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $error_message) { ?>
    <div><?= e($error_message) ?></div>
    <?php } ?>
</div>
<?php } ?>

<?php if ($show_success) { ?>
<div class="alert alert-success">
    <div><?= e($success_act === 'add' ? 'Thêm domain thành công.' : 'Cập nhật domain thành công.') ?></div>

    <br><a href="/cmd.php?cmd=/miz/bin/miz_lamp_deploy.sh" class="btn btn-success">CẬP NHẬT CÀI ĐẶT</a>
</div>
<?php } ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label" for="status">STATUS</label>
        <select id="status" name="status" class="form-select">
            <option value="on" <?= $forms['status'] === 'on' ? 'selected' : '' ?>>On</option>
            <option value="off" <?= $forms['status'] === 'off' ? 'selected' : '' ?>>Off</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">MODE</label>
        <select id="mode" name="mode" class="form-select">
            <?php foreach ($modes as $m) { ?>
            <option value="<?= e($m) ?>" <?= ($forms['mode'] == $m ? 'selected' : '') ?>>
                <?= e($m) ?>
            </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="domains">Domains</label>
        <textarea class="form-control" id="domains" name="domains" rows="3" placeholder="example.com *.example.com"><?= e($forms['domains']) ?></textarea>
    </div>

    <div class="mb-3 miz_mode miz_mode_php miz_mode_static" id="dir_group">
        <label class="form-label" for="dir">Dir</label>
        <input type="text" class="form-control" id="dir" name="dir" value="<?= e($forms['dir']) ?>" placeholder="web/miz.com">
        <div><small><code id="dir_demo"></code></small></div>
    </div>

    <div class="mb-3 miz_mode miz_mode_php miz_mode_static" id="dir_run_group">
        <label class="form-label" for="dir_run">Dir Run</label>
        <input type="text" class="form-control" id="dir_run" name="dir_run" value="<?= e($forms['dir_run']) ?>">
        <div class="form-text">Chỉ định thư mục chạy <code>public/</code> và sửa lỗi <code>open_basedir</code> khi code cần chạy nằm trong thư mục con của dự án , dùng khi CMS như Laravel, Symfony. Phải biết Code chạy thế nào mà quyết định đặt hay không!</div>
    </div>

    <div class="miz_mode miz_mode_php">
        <div class="mb-3">
            <label class="form-label" for="php">PHP</label>
            <select id="mode_php" name="mode_php" class="form-select">
                <?php foreach ($php_versions as $php_value => $php_label) { ?>
                <option value="<?= e($php_value) ?>" <?= $forms['mode_php'] === $php_value ? 'selected' : '' ?>>
                    <?= e($php_label) ?>
                </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="miz_mode miz_mode_proxy">
        <div class="mb-3">
            <label class="form-label">Proxy Config</label>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Scheme</label>
                <select name="mode_proxy_scheme" class="form-select">
                    <option value="http" <?= $forms['mode_proxy_scheme'] === 'http' ? 'selected' : '' ?>>http</option>
                    <option value="https" <?= $forms['mode_proxy_scheme'] === 'https' ? 'selected' : '' ?>>https</option>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Host</label>
                <input type="text" class="form-control" name="mode_proxy_host" value="<?= e($forms['mode_proxy_host']) ?>" placeholder="127.0.0.1">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Port</label>
                <input type="text" class="form-control" name="mode_proxy_port" value="<?= e($forms['mode_proxy_port']) ?>" placeholder="8080">
            </div>
        </div>

        
    </div>

    <div class="mb-3">
        <label class="form-label" for="ssl_mode">SSL Mode</label>
        <select id="ssl_mode" name="ssl_mode" class="form-select">
            <option value="auto" <?= $forms['ssl_mode'] === 'auto' ? 'selected' : '' ?>>Auto</option>
            <option value="self_signed" <?= $forms['ssl_mode'] === 'self_signed' ? 'selected' : '' ?>>Self Signed</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="max_conn">Max Connection</label>
        <input type="number" class="form-control" id="max_conn" name="max_conn" value="<?= e($forms['max_conn']) ?>">
        <div class="form-text text-danger">Giới hạn request/s. Chỉ dùng khi bị DDOS hoặc có lý do đặc biệt như bảo mật thư mục,...</div>
    </div>

    <div class="d-flex gap-2 mb-0">
        <button type="submit" class="btn btn-primary"><?= $is_edit_mode ? 'Lưu' : 'Thêm' ?></button>
        <a class="btn btn-outline-secondary" href="/domain_list.php">Hủy</a>
    </div>

    <hr class="mt-3 mb-0">

    <div class="mt-3 mb-3" style="font-size: 12px;">
        <label class="form-label">Custom CONFIG (nâng cao, không chỉnh lung tung)</label>
        <ul class="nav nav-pills flex-wrap gap-2" id="custom-config-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-secondary active" id="haproxy-tab" data-bs-toggle="tab" data-bs-target="#haproxy-pane" type="button" role="tab" aria-controls="haproxy-pane" aria-selected="true">Haproxy</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-outline-secondary" id="apache-tab" data-bs-toggle="tab" data-bs-target="#apache-pane" type="button" role="tab" aria-controls="apache-pane" aria-selected="false">Apache</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-outline-secondary" id="php-fpm-tab" data-bs-toggle="tab" data-bs-target="#php-fpm-pane" type="button" role="tab" aria-controls="php-fpm-pane" aria-selected="false">PHP-FPM</button>
            </li>
        </ul>

        <div class="tab-content mt-2" id="custom-config-tab-content">
            <div class="tab-pane fade show active" id="haproxy-pane" role="tabpanel" aria-labelledby="haproxy-tab" tabindex="0">
                <textarea class="form-control font-monospace" name="mode_haproxy_tpl" rows="14" style="white-space: nowrap; overflow-x: auto; font-size: 12px;"><?= e($forms['mode_haproxy_tpl']) ?></textarea>

                <div class="mt-3 mb-1"><i>default</i></div>
                <pre class="code-custom" style="font-size: 12px;"><?= e(@file_get_contents("$conf_path/haproxy.conf")) ?></pre>
            </div>

            <div class="tab-pane fade" id="apache-pane" role="tabpanel" aria-labelledby="apache-tab" tabindex="0">
                <textarea class="form-control font-monospace" name="mode_apache_tpl" rows="14" style="white-space: nowrap; overflow-x: auto; font-size: 12px;"><?= e($forms['mode_apache_tpl']) ?></textarea>

                <div class="mt-3 mb-1"><i>default</i></div>
                <pre class="code-custom" style="font-size: 12px;"><?= e(@file_get_contents("$conf_path/apache.conf")) ?></pre>
            </div>

            <div class="tab-pane fade" id="php-fpm-pane" role="tabpanel" aria-labelledby="php-fpm-tab" tabindex="0">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">max_process</label>
                        <input type="text" class="form-control" name="mode_php_fpm_max_process" placeholder="max_process" value="<?= e($forms['mode_php_fpm_max_process']) ?>">
                    </div>
                    
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">ini_open_basedir</label>
                        <input type="text" class="form-control" name="mode_php_fpm_ini_open_basedir" placeholder="ini_open_basedir" value="<?= e($forms['mode_php_fpm_ini_open_basedir']) ?>">
                    </div>
                </div>
                <label class="form-label mb-1">ini</label>
                <div class="form-text mb-1">Chỉ chấp nhận ini set: <code>php_value</code>, <code>php_admin_value</code>.</div>
                <textarea class="form-control font-monospace" name="mode_php_fpm_ini" rows="14" placeholder="php_value[ini_key] = value..." style="white-space: nowrap; overflow-x: auto; font-size: 12px;"><?= e($forms['mode_php_fpm_ini']) ?></textarea>

                <div class="mt-3 mb-1"><i>default</i></div>
                <pre class="code-custom" style="font-size: 12px;"><?= e(@file_get_contents("$conf_path/php-fpm-pool.conf")) ?></pre>
            </div>
        </div>
    </div>
</form>

<script>
$(() => {
    const $dir = $("#dir");
    const $dirRun = $("#dir_run");
    const $demo = $("#dir_demo");

    const normalize_dir = (value) => {
        const clean = (value || "").trim();
        return clean === "" ? "" : clean.replace(/^\/+|\/+$/g, "");
    };

    const sync_demo = () => {
        const d = normalize_dir($dir.val());
        const r = normalize_dir($dirRun.val());
        let base = "/home/www" + (d ? "/" + d : "");
        if (r) {
            base += '/<span style="color:green">' + r + '</span>';
        }
        $demo.html(base);
    };

    $dir.on("input", sync_demo);
    $dirRun.on("input", sync_demo);
    sync_demo();
});

$(() => {
    const $mode = $("#mode");
    const toggle_mode = () => {
        const val = $mode.val();
        $(".miz_mode").hide();
        $(`.miz_mode_${val}`).show();
    };
    $mode.on("change", toggle_mode);
    toggle_mode();
});

</script>

<?php require SITE_FOOTER; ?>
