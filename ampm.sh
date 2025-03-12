#!/bin/bash

INSTALLED_FILE=/etc/ngatngay/installed

installed() {
    [[ -f $INSTALLED_FILE ]]
}

list_php() {
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

install_php() {
    local version="$1"

    if [[ -z "$version" ]]; then
        echo "Usage: install_php <php_version>"
        return 1
    fi

    local packages=(
        "php${version}"
        "php${version}-apcu"
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
    
    sudo apt update
    sudo apt install -y "${packages[@]}"
}
