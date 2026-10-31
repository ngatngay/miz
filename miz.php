<?php

defined('ACCESS') or exit('Not access');

@ini_set('display_errors', true);
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', 3600);

error_reporting(-1);

// Kiểm tra user root
if (php_sapi_name() === 'cli') {
    @ini_set('display_errors', 'Off');
    @ini_set('log_errors', 'On');

    $is_root = false;

    if (function_exists('posix_geteuid')) {
        $is_root = posix_geteuid() === 0;
    } else {
        $user = getenv('USER') ?: getenv('USERNAME');
        $is_root = ($user === 'root');
    }

    if (!$is_root) {
        exit("Chỉ user root mới được phép chạy.\n");
    }
}

function dump(...$vars)
{
    var_dump(...$vars);
}

function dd(...$vars)
{
    dump(...$vars);
    exit;
}

function e($str)
{
    return htmlspecialchars((string) $str);
}

function str_replace_file($find, $replace, $file)
{
    // Check if file exists and is readable
    if (!file_exists($file) || !is_readable($file)) {
        return false;
    }

    // Read file contents
    $content = file_get_contents($file);
    if ($content === false) {
        return false;
    }

    // Replace text
    $content = str_replace($find, $replace, $content);

    // Write back to file
    if (!is_writable($file)) {
        return false;
    }

    return file_put_contents($file, $content) !== false;
}

class miz
{
    public static function get_sys_cpu()
    {
        $cpu_info = shell_exec("nproc"); // Lấy số lượng CPU core
        return (int)$cpu_info;
    }

    public static function mkdir(string $path, int $permission = 0700): void
    {
        if (!is_dir($path)) {
            mkdir($path, $permission, true);
        }
    }

    public static function get_tpl(string $name): string
    {
        $file = '/miz/tpl/' . $name;

        if (is_file($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    public static function gen_tpl(string $name, array $data): string
    {
        $content = self::get_tpl($name);

        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $content = str_replace('{{ $' . $key . ' }}', $value, $content);
            }
        }

        return $content;
    }

    public static function tpl_replace_block(string $content, string $name, string $replace): string
    {
        $pattern = '/(#' . preg_quote($name, '/') . '_start)(.*?)(#' . preg_quote($name, '/') . '_end)/s';
        return preg_replace($pattern, "$1\n" . $replace . "\n$3", $content);
    }
}


///
///
///


foreach (glob(__DIR__ . '/lib/miz_*.php') as $f) {
    include $f;
}
