<?php

define('ACCESS', true);

require __DIR__ . '/_init.php';

$domains = domains_load();
$id = (string) ($_GET['id'] ?? '');
$is_edit_mode = domain_exists($id);
$editing_domain = $is_edit_mode ? $domains[$id] : null;

$php_choices = [
    '0' => 'Static (không dùng PHP)',
    '5.6' => 'PHP 5.6',
    '7.4' => 'PHP 7.4',
    '8.0' => 'PHP 8.0',
    '8.1' => 'PHP 8.1',
    '8.2' => 'PHP 8.2',
    '8.3' => 'PHP 8.3',
];

$form_defaults = domain_config_tpl();
$editing_data = $editing_domain ?? [];
$form_values = array_merge($form_defaults, $editing_data);

$legacy_proxy_value = $editing_data['proxy'] ?? null;
$legacy_proxy_uri = $editing_data['proxy_uri'] ?? null;
$legacy_proxy_global_uri = $editing_data['proxy_global_uri'] ?? null;
if ($legacy_proxy_value === 'on' && !array_key_exists('mode', $editing_data)) {
    $form_values['mode'] = 'proxy';
}
if (!array_key_exists('apache_custom_global', $editing_data) && $legacy_proxy_global_uri !== null) {
    $form_values['apache_custom_global'] = $legacy_proxy_global_uri;
}
unset($form_values['proxy'], $form_values['proxy_global'], $form_values['proxy_global_uri']);

$available_modes = ['static', 'php', 'proxy', 'nodejs', 'custom'];
$selected_mode = 'static';
if (isset($form_values['mode']) && in_array($form_values['mode'], $available_modes, true)) {
    $selected_mode = $form_values['mode'];
} else {
    if ($legacy_proxy_value === 'on') {
        $selected_mode = 'proxy';
    } elseif (trim((string) $form_values['php']) !== '0') {
        $selected_mode = 'php';
    }
}
$form_values['mode'] = $selected_mode;

$errors = [];
$success_message = '';

if ($id !== '' && !$is_edit_mode) {
    $errors[] = 'Domain không tồn tại.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_mode = trim((string) ($_POST['mode'] ?? ''));
    if (in_array($posted_mode, $available_modes, true)) {
        $selected_mode = $posted_mode;
    }

    $form_values['status'] = trim((string) ($_POST['status'] ?? ''));
    $form_values['dir'] = trim((string) ($_POST['dir'] ?? ''));
    $form_values['domains'] = preg_replace('/\s+/', ' ', trim((string) ($_POST['domains'] ?? '')));
    $form_values['php'] = trim((string) ($_POST['php'] ?? ''));
    $form_values['apache_custom_global'] = trim((string) ($_POST['apache_custom_global'] ?? ''));
    $form_values['proxy_uri'] = trim((string) ($_POST['proxy_uri'] ?? ''));
    $form_values['apache_custom'] = trim((string) ($_POST['apache_custom'] ?? ''));

    if ($selected_mode === 'static') {
        $form_values['php'] = '0';
    }
    $form_values['mode'] = $selected_mode;

    $domain_names = array_values(array_filter(array_unique(preg_split('/\s+/', $form_values['domains']))));
    if (empty($domain_names)) {
        $errors[] = 'Vui lòng nhập ít nhất một domain.';
    }

    if ($form_values['php'] === '') {
        $errors[] = 'Vui lòng chọn phiên bản PHP.';
    } elseif (!array_key_exists($form_values['php'], $php_choices)) {
        $errors[] = 'Phiên bản PHP không hợp lệ.';
    }

    if (empty($errors)) {
        $existing_lookup = [];

        foreach ($domains as $existing_id => $existing_domain_config) {
            if ($is_edit_mode && $existing_id === $id) {
                continue;
            }

            $existing_list = array_filter(preg_split('/\s+/', trim((string) ($existing_domain_config['domains'] ?? ''))));
            foreach ($existing_list as $existing_domain_name) {
                $existing_lookup[strtolower($existing_domain_name)] = true;
            }
        }

        $duplicate_domains = [];
        foreach ($domain_names as $domain_name) {
            if (isset($existing_lookup[strtolower($domain_name)])) {
                $duplicate_domains[] = $domain_name;
            }
        }

        if (!empty($duplicate_domains)) {
            $errors[] = 'Các domain đã tồn tại: ' . implode(', ', $duplicate_domains);
        }
    }

    if (empty($errors)) {
        $target_id = $is_edit_mode ? $id : gen_uuid();

        $form_values['domains'] = implode(' ', $domain_names);
        $domains[$target_id] = array_merge(
            domain_config_tpl(),
            $domains[$target_id] ?? [],
            $form_values
        );

        domains_dump($domains);

        header('Location: /#domain-' . $target_id);
        exit;
    }
}

$page_heading = $is_edit_mode ? 'Cập nhật domain' : 'Thêm domain';
$site_title = $page_heading;

require '_header.php';

?>

<div class="title"><a href="index.php"><i class="layui-icon layui-icon-home"></i></a> <?= e($page_heading) ?></div>

<div class="content">
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error_message) { ?>
                <div><?= e($error_message) ?></div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($success_message !== '') { ?>
        <div class="alert alert-success"><?= e($success_message) ?></div>
    <?php } ?>

    <form class="layui-form" method="post">
        <div class="layui-form-item">
            <label for="status">Status</label>
            <select id="status" name="status" class="layui-select">
                <option value="on" <?= $form_values['status'] === 'on' ? 'selected' : '' ?>>
                    On
                </option>
                <option value="off" <?= $form_values['status'] === 'off' ? 'selected' : '' ?>>
                    Off
                </option>
            </select>
        </div>

        <div class="layui-form-item">
            <label for="dir">Thư mục</label>
            <input type="text" class="layui-input" id="dir" name="dir" value="<?= e($form_values['dir']) ?>" placeholder="/www/web/example.com">
        </div>

        <div class="layui-form-item">
            <label for="domains">Domains</label>            
            <textarea class="layui-textarea" id="domains" name="domains" placeholder="example.com *.example.com"><?= e($form_values['domains']) ?></textarea>
            <div class="layui-form-mid layui-word-aux">Ngăn cách các domain bằng dấu cách.</div>
        </div>

        <div class="layui-form-item">
            <label for="mode">Mode</label>
            <select id="mode" name="mode" class="layui-select" lay-filter="mode">
                <option value="static" <?= $selected_mode === 'static' ? 'selected' : '' ?>>Static</option>
                <option value="php" <?= $selected_mode === 'php' ? 'selected' : '' ?>>PHP</option>
                <option value="proxy" <?= $selected_mode === 'proxy' ? 'selected' : '' ?>>Proxy</option>
                <option value="nodejs" <?= $selected_mode === 'nodejs' ? 'selected' : '' ?>>Nodejs</option>
                <option value="custom" <?= $selected_mode === 'custom' ? 'selected' : '' ?>>Custom</option>
            </select>
        </div>

        <div class="mode-section<?= $selected_mode === 'static' ? '' : ' layui-hide' ?>" data-mode="static">
            <div class="layui-form-item">
                <div class="layui-form-mid layui-word-aux">Chế độ static phục vụ nội dung tĩnh, không chạy PHP.</div>
            </div>
        </div>

        <div class="mode-section<?= $selected_mode === 'php' ? '' : ' layui-hide' ?>" data-mode="php">
            <div class="layui-form-item">
                <label for="php">PHP</label>
                <select id="php" name="php" class="layui-select">
                    <?php foreach ($php_choices as $php_value => $php_label) { ?>
                        <option value="<?= e($php_value) ?>" <?= $form_values['php'] === $php_value ? 'selected' : '' ?>>
                            <?= e($php_label) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="mode-section<?= $selected_mode === 'proxy' ? '' : ' layui-hide' ?>" data-mode="proxy">
            <div class="layui-form-item">
                <label for="proxy_uri">Proxy URI</label>
                <textarea class="layui-textarea" id="proxy_uri" name="proxy_uri" placeholder="/ http://localhost:8080"><?= e($form_values['proxy_uri']) ?></textarea>
                <div class="layui-form-mid layui-word-aux">Mỗi dòng định nghĩa một proxy dạng &lt;path&gt; &lt;upstream&gt;.</div>
            </div>
        </div>

        <div class="mode-section<?= $selected_mode === 'nodejs' ? '' : ' layui-hide' ?>" data-mode="nodejs">
            <div class="layui-form-item">
                <div class="layui-form-mid layui-word-aux">Thiết lập Nodejs sẽ được cập nhật sau.</div>
            </div>
        </div>

        <div class="mode-section<?= $selected_mode === 'custom' ? '' : ' layui-hide' ?>" data-mode="custom">
            <div class="layui-form-item">
                <label for="apache_custom">Apache custom (tự cấu hình a-z)</label>
                <textarea class="layui-textarea" id="apache_custom" name="apache_custom" placeholder="# Custom Apache directives"><?= e($form_values['apache_custom']) ?></textarea>
                <div class="layui-form-mid layui-word-aux">Nhập cấu hình Apache tùy chỉnh (chỉ có domain và ssl và log là có sẵn, còn lại phải tự thêm).</div>
            </div>
        </div>

        <div class="layui-form-item<?= $selected_mode === 'custom' ? ' layui-hide' : '' ?>" id="apache-custom-global">
            <label for="apache_custom_global">Apache Custom toàn cục</label>
            <textarea class="layui-textarea" id="apache_custom_global" name="apache_custom_global" placeholder="# Custom Apache directives"><?= e($form_values['apache_custom_global']) ?></textarea>
            <div class="layui-form-mid layui-word-aux">Cấu hình Apache "bổ sung" vào cấu hình hiện tại, áp dụng cho toàn bộ hệ thống.</div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn layui-btn-normal"><?= $is_edit_mode ? 'Lưu' : 'Thêm' ?></button>
                <a class="layui-btn layui-btn-primary" href="/">Hủy</a>
            </div>
        </div>
    </form>
</div>

<script>
    var $ = layui.$;
    var form = layui.form;
    var $modeSelect = $('#mode');
    var $sections = $('.mode-section');
    var $apacheCustomGlobal = $('#apache-custom-global');

    function updateMode() {
        var current = $modeSelect.val();
        $sections.addClass('layui-hide');
        $sections.filter('[data-mode="' + current + '"]').removeClass('layui-hide');
        $apacheCustomGlobal.toggleClass('layui-hide', current === 'custom');
    }

    $modeSelect.on('change', updateMode);
    form.on('select(mode)', function() {
        updateMode();
    });
    form.render();
    updateMode();
</script>

<?php

require '_footer.php';
