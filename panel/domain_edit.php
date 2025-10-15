<?php

define('ACCESS', true);

require __DIR__ . '/../miz.php';

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
$form_values = array_merge($form_defaults, $editing_domain ?? []);

$errors = [];
$success_message = '';

if ($id !== '' && !$is_edit_mode) {
    $errors[] = 'Domain không tồn tại.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_values['dir'] = trim((string) ($_POST['dir'] ?? ''));
    $form_values['domains'] = preg_replace('/\s+/', ' ', trim((string) ($_POST['domains'] ?? '')));
    $form_values['php'] = trim((string) ($_POST['php'] ?? ''));

    if ($form_values['dir'] === '') {
        $errors[] = 'Vui lòng nhập thư mục chứa mã nguồn.';
    }

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
            [
                'dir' => $form_values['dir'],
                'domains' => $form_values['domains'],
                'php' => $form_values['php'],
            ]
        );

        domains_dump($domains);

        header('Location: /#domain-' . $target_id);
        exit;
    }
}

$page_heading = $is_edit_mode ? 'Cập nhật domain' : 'Thêm domain';
$site_title = $page_heading;

require 'header.php';

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
            <label for="dir">Thư mục</label>
            <input type="text" class="layui-input" id="dir" name="dir" value="<?= e($form_values['dir']) ?>" placeholder="/www/web/example.com">
        </div>

        <div class="layui-form-item">
            <label for="domains">Domains</label>            
            <textarea class="layui-textarea" id="domains" name="domains" placeholder="example.com *.example.com"><?= e($form_values['domains']) ?></textarea>
            <div class="layui-form-mid layui-word-aux">Ngăn cách các domain bằng dấu cách.</div>
        </div>

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

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn layui-btn-normal"><?= $is_edit_mode ? 'Lưu' : 'Thêm' ?></button>
                <a class="layui-btn layui-btn-primary" href="/">Hủy</a>
            </div>
        </div>
    </form>
</div>

<?php

require 'footer.php';
