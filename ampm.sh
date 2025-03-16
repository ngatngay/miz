#!/bin/bash

INSTALLED_FILE="/etc/ngatngay/installed"

installed() {
    [[ -f $INSTALLED_FILE ]]
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
