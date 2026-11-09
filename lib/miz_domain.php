<?php

class miz_domain
{
    public static function config_tpl()
    {
        return [
            'domains' => '',
            'status' => 'on', // on/off

            'dir' => '',
            'dir_run' => '',
            'ssl_mode' => 'auto',
            'max_conn' => '0',

            'mode' => 'php',

            'mode_proxy_scheme' => 'http',
            'mode_proxy_host' => '127.0.0.1',
            'mode_proxy_port' => '8080',
            

            'mode_haproxy_tpl' => '',
            'mode_apache_tpl' => '',

            'mode_php' => '0', // 0 - disable
            'mode_php_fpm_ini' => '',
            'mode_php_fpm_max_process' => '',
            
            'mode_php_fpm_ini_open_basedir' => '',
        ];
    }

    public static function get($key)
    {
        $file = "/miz-data/domains/$key/data.json";
        $data = [];

        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true);
        }

        return array_replace(self::config_tpl(), $data);
    }

    public static function save(string $key, array $data): void
    {
        $dir = "/miz-data/domains/$key";

        miz::mkdir($dir);

        file_put_contents(
            "$dir/data.json",
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    public static function get_list()
    {
        $domains = [];

        foreach (self::get_all() as $domain) {
            $items = self::parse_domains($domain['domains']);

            foreach ($items as $item) {
                $domains[$item] = true;
            }
        }

        return array_keys($domains);
    }

    public static function get_all(): array
    {
        $domains = [];

        foreach (glob('/miz-data/domains/*/data.json') as $file) {
            $id = basename(dirname($file));
            $domains[$id] = self::get($id);
        }

        return $domains;
    }

    public static function parse_id(string $domains): string
    {
        $parts = self::parse_domains($domains);
        $domain = $parts[0] ?? '';
        return preg_replace('/[^A-Za-z0-9._-]/', '_', trim($domain));
    }

    public static function parse_domains($data)
    {
        return preg_split(
            '/\s+/',
            $data,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
    }

    public static function unset(string $id)
    {
        $id = trim($id);

        if (empty($id)) {
            return;
        }

        $path = '/miz-data/domains/' . $id;
        passthru('rm -rf ' . escapeshellarg($path));
    }
}
