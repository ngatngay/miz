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
domain_config_dir=/www/data/domain/$domain

mkdir -p $domain_config_dir

# tpl

tpl_php=$(cat <<EOT
<FilesMatch "\.php$">
    SetHandler "proxy:unix:/run/php/php-fpm-${domain}.sock|fcgi://localhost"
</FilesMatch>
EOT
)

export tpl_domains=$domains
export tpl_dir=$domain_path
export tpl_php=$php_version

declare -px | grep '^declare -x tpl_' > $domain_config_dir/config.sh

echo "created: $domain"
echo "---"
echo "please run update"
