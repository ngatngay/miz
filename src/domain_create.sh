#!/bin/bash

default_php="8.3"

# domain
echo "Domain (domain.com alias.domain.com): "
read -e -p "> " domains

for d in $domains; do
    if ! domain_valid "$d"; then
        echo "${d} khong hop le."
        exit
    fi
done

domain=$(awk '{print $1}' <<< "$domains")

# path
tmp_path="/srv/${domain}"
echo "Path (${tmp_path}):"
read -e -p "> " domain_path

if [ -z "$domain_path" ]; then
    domain_path=$tmp_path
fi

mkdir -p $domain_path

# php
echo "PHP version (default 8.3, 0 to disable): "
read -e -p "> " php_version

if [ -z "$php_version" ]; then
    php_version=$default_php
fi

echo

# gen config
domain_config_dir=/etc/ngatngay/domain/$domain

mkdir -p $domain_config_dir

# tpl
if [ "$php_version" != "0" ]; then
tpl_php=$(cat <<EOT
<FilesMatch "\.php$">
    SetHandler "proxy:unix:/run/php/php${php_version}-fpm-${domain}.sock|fcgi://localhost"
</FilesMatch>
EOT
)
fi

export tpl_domain=$domain
export tpl_domains=$domains
export tpl_dir=$domain_path
export tpl_php_version=$php_version
export tpl_php=$tpl_php

declare -px | grep '^declare -x tpl_' > $domain_config_dir/config.sh

envsubst < ${ROOT_PATH}/tpl/apache_vhost.conf > $domain_config_dir/apache.conf
envsubst < ${ROOT_PATH}/tpl/php-fpm.conf > $domain_config_dir/php-fpm.conf

echo "created: $domain"
