<?php

define('ACCESS', true);

require 'miz.php';

$data = [];

$config_files = glob('/www/data/domain/*/config.sh');

foreach ($config_files as $file_path) {
    $domain_dir = basename(dirname($file_path)); // tên thư mục con, ví dụ: abc.com

    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $config = [];

    foreach ($lines as $line) {
        if (preg_match('/^declare -x tpl_([^=]+)="?(.*?)"?$/', $line, $matches)) {
            $key = $matches[1];
            $value = $matches[2];
            $config[$key] = $value;
        }
    }

    if (!empty($config)) {
        $data[gen_uuid()] = array_merge(domain_config_tpl(), $config);
    }
}

// In kết quả để kiểm tra
print_r($data);

domains_dump($data);
