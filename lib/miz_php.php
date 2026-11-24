<?php

class miz_php
{
    public static function list_fpm(): array
    {
        $versions = [];

        foreach (glob('/etc/php/*/fpm/php.ini') ?: [] as $ini) {
            $versions[] = basename(dirname(dirname($ini)));
        }

        usort($versions, 'version_compare');

        return $versions;
    }
}
