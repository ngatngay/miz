# php fpm www
for p in $(php_list); do
    pool_dir=/etc/php/${p}/fpm/pool.d
    
    export tpl_php_version=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

exit
dir="/etc/ngatngay/domain"

for d in $dir/*/; do
    [[ ! -d "$d" ]] && continue

    source $d/.config.sh
    echo $d
done

systemctl restart php${php_version}-fpm

a2ensite $domain
systemctl restart apache2
