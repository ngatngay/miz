# apache
cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/ngatngay.conf
a2enconf ngatngay

a2dissite '*'

rm -f /etc/apache2/sites-available/*

cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default

cp ${ROOT_PATH}/tpl/apache_vhost_default_ssl.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default_ssl

# php fpm
for p in $(php_list); do
    pool_dir=/etc/php/${p}/fpm/pool.d
    
    rm -f ${pool_dir}/*

    export tpl_php_version=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

# for domain
dir="/etc/ngatngay/domain"

for d in $dir/*/; do
    [[ ! -d "$d" ]] && continue

    source $d/config.sh

    if [ "$tpl_php_version" == "0" ]; then
        export tpl_php=""
    fi

    # apache
    if [ ! -f "$d/apache.conf" ]; then
        cp $ROOT_PATH/tpl/apache_vhost.conf $d/apache.conf
    fi

    envsubst < $d/apache.conf > /etc/apache2/sites-available/${tpl_domain}.conf
    a2ensite $tpl_domain
    
    # php
    if [ ! -f "$d/php-fpm.conf" ]; then
        cp $ROOT_PATH/tpl/php-fpm.conf $d/php-fpm.conf
    fi

    if [ "$tpl_php_version" != "0" ]; then
        envsubst < ${d}/php-fpm.conf > /etc/php/${tpl_php_version}/fpm/pool.d/${tpl_domain}.conf
    fi
done

#php
for p in $(php_list); do
    systemctl restart php${p}-fpm
done

# apache
systemctl restart apache2
