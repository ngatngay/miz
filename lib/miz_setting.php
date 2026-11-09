<?php

class miz_setting
{
    public static function config_tpl(): array
    {
        return [
            'http_keepalive' => '60',
        ];
    }

    public static function save(array $data): void
    {
        file_put_contents(
            '/miz-data/settings.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    public static function get(string $key): string
    {
        $data = self::get_all();
        
        if (!isset($data[$key])) {
            return '';
        }

        if (!empty($data[$key])) {
            return trim((string) $data[$key]);
        } else {
            return trim((string) self::config_tpl()[$key]);
        }
    }
    
    public static function get_all()
    {
        $data = [];

        if (is_file('/miz-data/settings.json')) {
            $data = json_decode(
                file_get_contents('/miz-data/settings.json'),
                true
            );
        }

        return array_replace(self::config_tpl(), $data);
    }
}
