<?php

class miz_cron
{
    public static function config_tpl(): array
    {
        return [
            'desc' => '',
            'user' => 'www',
            'expression' => '',
            'cmd' => '',
        ];
    }

    public static function get_all(): array
    {
        if (!is_file('/miz_data/crontab.json')) {
            return [];
        }

        $items = json_decode(file_get_contents('/miz_data/crontab.json'), true);

        if (!is_array($items)) {
            return [];
        }

        return $items;
    }

    public static function save_all(array $items): void
    {
        uasort($items, function (array $a, array $b): int {
            return strcmp($a['user'], $b['user']);
        });

        file_put_contents(
            '/miz_data/crontab.json',
            json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        self::deploy();
    }

    public static function add(array $data): void
    {
        $items = self::get_all();

        $items[uniqid('', true)] = $data;

        self::save_all($items);
    }

    public static function update(string $id, array $data): void
    {
        $items = self::get_all();

        $items[$id] = $data;

        self::save_all($items);
    }

    public static function delete(string $id): void
    {
        $items = self::get_all();

        unset($items[$id]);

        self::save_all($items);
    }

    public static function deploy(): void
    {
        if (!is_file('/miz_data/crontab.json')) {
            return;
        }

        $lines = [];

        foreach (self::get_all() as $item) {
            $item = array_replace(self::config_tpl(), $item);

            if ($lines) {
                $lines[] = '';
            }

            $text = str_replace(["\r\n", "\r"], "\n", (string) $item['desc']);

            foreach (explode("\n", $text) as $line) {
                $lines[] = '# ' . $line;
            }

            $lines[] = $item['expression'] . ' ' . $item['user'] . ' ' . $item['cmd'];
        }

        file_put_contents('/etc/cron.d/miz_crontab', implode("\n", $lines) . "\n");
        chmod('/etc/cron.d/miz_crontab', 0644);
    }
}
