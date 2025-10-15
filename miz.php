<?php

defined('ACCESS') or exit('Not access');

@ini_set('display_errors', true);
@ini_set('memory_limit', '64M');
@ini_set('max_execution_time', 60);
@ini_set('opcache.revalidate_freq', 0);

error_reporting(-1);

define('miz_path', '/www/miz');
define('app_path', '/www/app');
define('panel_path', '/www/miz/panel');
define('data_path', '/www/data');
define('web_path', '/www/web');

function gen_uuid(): string {
    usleep(5000);
    $time = (int) (microtime(true) * 1000);
    $time_hex = str_pad(dechex($time), 12, '0', STR_PAD_LEFT);
    $random_bytes = random_bytes(10);
    $random_hex = bin2hex($random_bytes);
    $version = '7' . substr($time_hex, 1);
    $variant_hex = substr($random_hex, 0, 4);
    $variant_int = hexdec($variant_hex);
    $variant_int = ($variant_int & 0x3FFF) | 0x8000;
    $variant_hex = str_pad(dechex($variant_int), 4, '0', STR_PAD_LEFT);
    $uuid = sprintf(
        '%s-%s-%s-%s-%s',
        substr($time_hex, 0, 8),
        substr($time_hex, 8, 4),
        substr($version, 0, 4),
        $variant_hex,
        substr($random_hex, 4, 12)
    );
    return strtolower($uuid);
}

function domains_load() {
    $path = data_path . '/domains.json';
    
    if (!file_exists($path)) {
        return [];
    }

    return (array) json_decode(file_get_contents($path), true);
}

function domains_dump(array $data) {
    foreach ($data as $key => $value) {
        $data[$key] = array_merge(domain_config_tpl(), $value);
    }

    file_put_contents(
        data_path . '/domains.json',
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}

function domain_exists(string $id) {
    $id = trim($id);
    if ($id === '') {
        return false;
    }

    $domains = domains_load();

    return array_key_exists($id, $domains);
}

function domain_config_tpl() {
    return [
        'domains' => '',
        'status' => 'on', // on/off
        'dir' => '',
        'php' => '0' // 0 - disable/5.6/7.4
    ];
}

function e($str) {
    return htmlspecialchars($str);
}
