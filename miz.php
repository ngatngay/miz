<?php

defined('ACCESS') or exit('Not access');

@ini_set('display_errors', true);
@ini_set('memory_limit', '64M');
@ini_set('max_execution_time', 300);
@ini_set('opcache.revalidate_freq', 0);

error_reporting(-1);

define('miz_path', '/www/miz');
define('app_path', '/www/app');
define('panel_path', '/www/miz/panel');
define('data_path', '/www/data');
define('web_path', '/www/web');
define('setting_path', '/www/data/setting');

if (php_sapi_name() === 'cli') {
// Kiểm tra user root
$is_root = false;

if (function_exists('posix_geteuid')) {
    $is_root = posix_geteuid() === 0;
} else {
    $user = getenv('USER') ?: getenv('USERNAME');
    $is_root = ($user === 'root');
}

if (!$is_root) {
    die("Chỉ user root mới được phép chạy.\n");
}
}

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
        'mode' => 'php',
        'dir' => '',
        'php' => '0', // 0 - disable/5.6/7.4
        'proxy_uri' => '"/" "http://localhost:8080/"',
        'apache_custom_global' => '',
        'apache_custom' => ''
    ];
}

function e($str) {
    return htmlspecialchars($str);
}

function get_tpl(string $name): string {
    $paths = [
        '/www/data/tpl/',
        '/www/miz/tpl/'
    ];

    foreach ($paths as $path) {
        $file = $path . $name;

        if (is_file($file)) {
            return file_get_contents($file);
        }
    }

    return '';
}

function gen_tpl(string $name, array $data): string {
    $content = get_tpl($name);

    foreach ($data as $key => $value) {
        if (is_string($value) || is_numeric($value)) {
            $content = str_replace('{{ $' . $key . ' }}', $value, $content);
        }
    }
    
    return $content;
}

function tpl_replace_block(string $content, string $name, string $replace): string {
    $pattern = '/(#' . preg_quote($name, '/') . '_start)(.*?)(#' . preg_quote($name, '/') . '_end)/s';
    return preg_replace($pattern, "$1\n" . $replace . "\n$3", $content);
}

function get_tpl_domain(string $id, string $name): string {
    $file = '/www/data/domain/' . $id . '/' . $name;
    return is_file($file) ? file_get_contents($file) : '';
}

function put_tpl_domain(string $id, string $name, string $content): void {
    $file = '/www/data/domain/' . $id . '/' . $name;
    file_put_contents($file, $content);
}

function setting_get($key = '') {
    return (string) @file_get_contents(setting_path . '/' . $key);
}

function setting_set($key, $value) {
    return file_put_contents(setting_path . '/' . $key, $value);
}
