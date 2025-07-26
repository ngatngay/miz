#!/bin/bash

INSTALLED_FILE="/www/data/installed"
PHP_DEFAULT="8.3"

installed() {
    [[ -f $INSTALLED_FILE ]]
}

cmd_exists() {
    command -v "$1" >/dev/null 2>&1
}

efw() {
    local char="${1:--}"
    printf '%*s\n' "$(tput cols)" '' | tr ' ' "$char"
}

ehw() {
    local char="${1:--}"
    printf '%*s\n' "$(( $(tput cols) / 2 ))" '' | tr ' ' "$char"
}

cptpl() {
    local input="$1"
    local output="$2"
    local file="${ROOT_PATH}/tpl/${input}"

    if [ -f "${ROOT_PATH}/tpl_overwrite/${input}" ]; then
        file="${ROOT_PATH}/tpl_overwrite/${input}"
    fi
    
    cp $file $output
}

domain_valid() {
    local domain="$1"
    local regex="^([a-zA-Z0-9][-a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$"

    if [[ "$domain" =~ $regex ]]; then
        return 0
    else
        return 1
    fi
}


php_list() {
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

php_install() {
    local version="$1"

    if [[ -z "$version" ]]; then
        echo "Usage: install_php <php_version>"
        return 1
    fi

    local packages=(
        "php${version}"
        "php${version}-apcu"
        "php${version}-bcmath"
        "php${version}-memcached"
        "php${version}-redis"
        "php${version}-cli"
        "php${version}-curl"
        "php${version}-fpm"
        "php${version}-intl"
        "php${version}-gd"
        "php${version}-mbstring"
        "php${version}-mysql"
        "php${version}-sqlite3"
        "php${version}-xml"
        "php${version}-zip"
    )
    
    sudo apt-get update > /dev/null
    sudo apt install -y "${packages[@]}"
}

nginx_vhost_add() {
    local name="$1"
    local conf="$2"

    cp $conf /etc/nginx/sites-available/$name
    cp $conf /etc/nginx/sites-enabled/$name
}

websv_start() {
    echo 1
}

websv_stop() {
    sudo systemctl stop nginx

    rm -f /etc/nginx/sites-available/*
    rm -f /etc/nginx/sites-enabled/*
}
