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
