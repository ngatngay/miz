# cấu hình chung 1 lần, áp dụng sau khi cài đặt

echo "config-ing..."

# security system
cptpl fail2ban.filter.miz_admin.conf /etc/fail2ban/filter.d/
cptpl fail2ban.conf /etc/fail2ban/jail.local
sudo systemctl restart fail2ban

# log auto html
cp -p ${ROOT_PATH}/miz_gen_weblog.sh /etc/cron.daily/miz_gen_weblog

# log system limit
cp ${ROOT_PATH}/tpl/systemd-journald.conf /etc/systemd/journald.conf
systemctl restart systemd-journald

# log rotate
cptpl logrotate.apache.conf /etc/logrotate.d/miz_apache

#ssl
echo -e "#!/bin/bash\ncertbot renew --quiet" | sudo tee /etc/cron.daily/certbot-renew > /dev/null
sudo chmod +x /etc/cron.daily/certbot-renew





# php
for p in $(php_list); do
    #stop service
    systemctl stop php${p}-fpm

    # config
    cptpl php-cli.ini /etc/php/${p}/cli/conf.d/99-ngatngay.ini
    cptpl php.ini /etc/php/${p}/fpm/conf.d/99-ngatngay.ini

    #pool
    pool_dir=/etc/php/${p}/fpm/pool.d
    export tpl_php=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

#php
for p in $(php_list); do
    systemctl start php${p}-fpm
done

# apache module
a2enmod http2 ssl rewrite headers proxy proxy_http proxy_fcgi setenvif ratelimit 1>/dev/null

# apache conf
cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/zzz_ngatngay.conf
a2disconf '*' 1>/dev/null
a2enconf zzz_ngatngay 1>/dev/null

# vhost
cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/

apachectl configtest && systemctl restart apache2

# finish
echo "---"
echo "configed"
