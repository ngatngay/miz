#!/bin/bash

if installed; then
    echo "---"
    echo "script installed, only update"
    echo "---"
fi

#apt update
#apt upgrade

# apt install neovim git fish rclone restic

# init
# restic self-update

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
php_conf=$(cat << 'EOF'
open_basedir =

disable_functions =
disable_classes =

max_execution_time = 300
max_input_time = 600
max_input_vars = 1000000

memory_limit = 256M

error_reporting = E_ALL
display_errors = On
log_errors = On
error_log = error_log

post_max_size = 4096M
upload_max_filesize = 4096M
max_file_uploads = 200

date.timezone = "Asia/Ho_Chi_Minh"

apnable_cli=On
EOF
)

for f in $(list_php); do
    echo "$php_conf" > /etc/php/${f}/cli/conf.d/99-ngatngay.ini
    echo "$php_conf" > /etc/php/${f}/fpm/conf.d/99-ngatngay.ini
    
    systemctl restart php${f}-fpm.service
done


# apahce
echo "MDCertificateAgreement accepted
MDContactEmail ssl@gmail.com
MDStoreDir /var/www/apache-ssl
MDChallengeDns01 /var/www/bin/acme-apache
MDRequireHttps permanent
MDMembers manual
MDMatchNames servernames

LimitRequestFieldSize 65536
LimitRequestLine 65536

<Directory /srv>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>" > /etc/apache2/conf-available/ngatngay.conf

a2enconf ngatngay
systemctl reload apache2

echo 1 > $INSTALLED_FILE
