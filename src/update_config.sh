echo "updating..."

# log
cp -p ${ROOT_PATH}/ampm_gen_weblog.sh /etc/cron.daily/

cp ${ROOT_PATH}/tpl/systemd-journald.conf /etc/systemd/journald.conf
systemctl restart systemd-journald

# apache
# Bật module cần thiết
a2enmod md ssl headers rewrite proxy proxy_hcheck proxy_balancer proxy_fcgi proxy_http proxy_wstunnel 1>/dev/null

cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/ngatngay.conf
a2enconf ngatngay 1>/dev/null

a2dissite '*' 1>/dev/null

rm -f /etc/apache2/sites-available/*

cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default 1>/dev/null

cp ${ROOT_PATH}/tpl/apache_vhost_default_ssl.conf /etc/apache2/sites-available/
a2ensite apache_vhost_default_ssl 1>/dev/null

# php
for f in $(php_list); do
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/cli/conf.d/99-ngatngay.ini
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/fpm/conf.d/99-ngatngay.ini
done

for p in $(php_list); do
    pool_dir=/etc/php/${p}/fpm/pool.d
    
    rm -f ${pool_dir}/*

    export tpl_php_version=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

# for domain
dir="/opt/ampm_data/domain"

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

    export APACHE_LOG_DIR='${APACHE_LOG_DIR}'

    envsubst < $d/apache.conf > /etc/apache2/sites-available/${tpl_domain}.conf
    a2ensite $tpl_domain 1>/dev/null
    
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
    systemctl stop php${p}-fpm
done
for p in $(php_list); do
    systemctl start php${p}-fpm
done

# apache
systemctl restart apache2

echo "---"
echo "updated"
