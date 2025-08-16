# cấu hình chung 1 lần, áp dụng sau khi cài đặt

echo "config-ing..."

# security system
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

#ftp
cptpl vsftpd.conf /etc/vsftpd.conf
systemctl restart vsftpd

# apache
a2enmod http2 ssl rewrite headers proxy proxy_http proxy_fcgi setenvif ratelimit 1>/dev/null

cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/99-ngatngay.conf
a2disconf '*' 1>/dev/null
a2enconf 99-ngatngay 1>/dev/null

rm -f /etc/apache2/sites-available/*
cptpl apache_vhost_default.conf /etc/apache2/sites-available/99-ngatngay.conf

a2dissite '*' >/dev/null
a2ensite '*' >/dev/null

apachectl configtest && systemctl restart apache2

# php
for p in $(php_list); do
    systemctl stop php${p}-fpm

    # config
    cptpl php-cli.ini /etc/php/${p}/cli/conf.d/99-ngatngay.ini
    cptpl php.ini /etc/php/${p}/fpm/conf.d/99-ngatngay.ini

    #pool
    pool_dir=/etc/php/${p}/fpm/pool.d
    rm -f ${pool_dir}/*

    export tpl_php=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/99-www.conf
done
for p in $(php_list); do
    systemctl start php${p}-fpm
done

#www-data
mkdir -p /var/www
mkdir -p /var/www/.ssh
chown -R www-data:www-data /var/www

sudo usermod -s /usr/bin/fish www-data
sudo usermod -aG sudo www-data

echo
echo 'dat mat khau cho www-data (dang nhap ssh, ftp):'
sudo passwd www-data

echo
echo 'dat mau khau cho panel:'
bash -c 'miz_gen_admin.sh'

#end
echo
echo
echo "---"
echo "danh sach web da reset"
echo "can chay lai cap nhat tat ca"
echo "miz update_web"
echo "---"
echo "cau hinh thanh cong"
