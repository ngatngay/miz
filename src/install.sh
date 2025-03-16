#!/bin/bash

phps=("7.4" "8.3")

if installed; then
    echo "---"
    echo "script installed, only update"
    echo "---"
fi

apt update
apt upgrade

apt install neovim git fish rclone restic

# init
restic self-update

# mariadb
if ! installed; then
    sudo apt-get install apt-transport-https curl
    sudo mkdir -p /etc/apt/keyrings
    sudo curl -o /etc/apt/keyrings/mariadb-keyring.pgp 'https://mariadb.org/mariadb_release_signing_key.pgp'

    cat << 'EOF' > /etc/apt/sources.list.d/mariadb.sources
# MariaDB 10.11 repository list - created 2025-03-12 01:45 UTC
# https://mariadb.org/download/
X-Repolib-Name: MariaDB
Types: deb
# deb.mariadb.org is a dynamic mirror if your preferred mirror goes offline. See https://mariadb.org/mirrorbits/ for details.
# URIs: https://deb.mariadb.org/10.11/debian
URIs: https://vn-mirrors.vhost.vn/mariadb/repo/10.11/debian
Suites: bookworm
Components: main
Signed-By: /etc/apt/keyrings/mariadb-keyring.pgp
EOF

    sudo apt-get update
    sudo apt-get install mariadb-server
fi

# php
if ! installed; then
    for i in "${phps[@]}"; do
        install_php $i
    done
fi

for f in $(list_php); do
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/cli/conf.d/99-ngatngay.ini
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/fpm/conf.d/99-ngatngay.ini
    
    systemctl restart php${f}-fpm.service
done

# apahce
if ! installed; then
fi

# Bật module cần thiết
a2enmod md ssl headers rewrite proxy proxy_hcheck proxy_balancer proxy_fcgi proxy_http proxy_wstunnel

# cau hinh
cp $ROOT_PATH/tpl/apache.conf /etc/apache2/conf-available/ngatngay.conf

a2enconf ngatngay
systemctl restart apache2

echo 1 > $INSTALLED_FILE

echo
echo "cai dat thanh cong!"

