#!/usr/bin/env bash

PHP_DEFAULT="8.2"

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

miz_php_install() {
    if [[ $# -eq 0 ]]; then
        echo "Usage: miz_php_install <php_version> [more_versions...]"
        return 1
    fi

    local all_packages=()

    for version in "$@"; do
        all_packages+=(
            "php${version}"
            "php${version}-bcmath"
            "php${version}-memcached"
            "php${version}-redis"
            "php${version}-cli"
            "php${version}-curl"
            "php${version}-fpm"
            "php${version}-intl"
            "php${version}-imagick"
            "php${version}-imap"
            "php${version}-gd"
            "php${version}-mbstring"
            "php${version}-mcrypt"
            "php${version}-mysql"
            "php${version}-sqlite3"
            "php${version}-xml"
            "php${version}-xmlrpc"
            "php${version}-zip"
        )
    done

    echo "${all_packages[@]}"
}
