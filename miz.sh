#!/usr/bin/env bash

PHP_DEFAULT="8.3"

###

miz_cron_add() {
    local name="$1"

    cp -f "/miz/tpl/cron.d/${name}" "/etc/cron.d/${name}"
    chmod 0644 "/etc/cron.d/${name}"
}

miz_php_list() {
    if [ -d "/etc/php/" ]; then
        php_versions=()

        for version in /etc/php/*; do
            if [ -d "$version" ]; then
                php_versions+=("$(basename "$version")")
            fi
        done

        if [ ${#php_versions[@]} -gt 0 ]; then
            echo "${php_versions[@]}"
        fi
    fi
}
