# cấu hình chung 1 lần, áp dụng sau khi cài đặt

echo "config-ing..."

HTPASSWD_FILE="/www/data/admin.htpasswd"

# security system
(
    cd /tmp
    rm -rf fail2ban
    git clone --depth 1 https://github.com/fail2ban/fail2ban.git

    sudo cp -r fail2ban/config/* /etc/fail2ban/  

    cptpl fail2ban.conf /etc/fail2ban/jail.local
    sudo systemctl restart fail2ban
)

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

cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/0-ngatngay.conf
a2disconf '*' 1>/dev/null
a2enconf 0-ngatngay 1>/dev/null

rm -f /etc/apache2/sites-available/*
cptpl apache_vhost_default.conf /etc/apache2/sites-available/0-ngatngay.conf

a2dissite '*' >/dev/null
a2ensite '*' >/dev/null

apachectl configtest && systemctl restart apache2

# php
for p in $(php_list); do
    systemctl stop php${p}-fpm

    # config
    cptpl php-cli.ini /etc/php/${p}/cli/conf.d/0-ngatngay.ini
    cptpl php.ini /etc/php/${p}/fpm/conf.d/0-ngatngay.ini

    #pool
    pool_dir=/etc/php/${p}/fpm/pool.d
    rm -f ${pool_dir}/*

    export tpl_php=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/0-www.conf
done
for p in $(php_list); do
    systemctl start php${p}-fpm
done

echo
bash -c 'php /www/miz/src/update_web.php'
bash -c 'miz fix'

# doi mat khau
echo
echo 'dat mat khau cho www-data (dang nhap ssh, ftp)'
read -s -p "Nhập mật khẩu: " password
echo
read -s -p "Nhập lại mật khẩu: " password_confirm
echo

if [[ -z "$password" ]]; then
    echo "❌ Mật khẩu không được để trống."
    exit
fi

if [[ ${#password} -lt 8 ]]; then
    echo "❌ Mật khẩu phải dài ít nhất 8 ký tự."
    exit
fi

if [[ "$password" != "$password_confirm" ]]; then
    echo "❌ Mật khẩu nhập lại không khớp."
    exit
fi

#www-data
mkdir -p /var/www
mkdir -p /var/www/.ssh

sudo usermod -s /usr/bin/fish www-data
sudo usermod -aG sudo www-data
echo "www-data:$password" | sudo chpasswd

# Tạo file .htpasswd với user đầu tiên
htpasswd -cb "$HTPASSWD_FILE" "admin" "$password"

echo "✅ Đã tạo file $HTPASSWD_FILE."
echo "✅ user: admin"
echo "✅ Bạn có thể đăng nhập tại: https://[IP]:9869"

#end
echo
echo "---"
echo "cau hinh thanh cong"
