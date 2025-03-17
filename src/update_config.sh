# apache
# Bật module cần thiết
a2enmod md ssl headers rewrite proxy proxy_hcheck proxy_balancer proxy_fcgi proxy_http proxy_wstunnel

cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/ngatngay.conf
a2enconf ngatngay

a2dissite '*'

rm -f /etc/apache2/sites-available/*

cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default

cp ${ROOT_PATH}/tpl/apache_vhost_default_ssl.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default_ssl

# php
for f in $(php_list); do
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/cli/conf.d/99-ngatngay.ini
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/fpm/conf.d/99-ngatngay.ini
    
    systemctl restart php${f}-fpm
done

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
