echo "updating..."

# update ssl
read -p "Do you update SSL? [y/N]: " confirm_ssl
confirm_ssl="${confirm_ssl,,}"

# log
cp -p ${ROOT_PATH}/miz_gen_weblog.sh /etc/cron.daily/

cp ${ROOT_PATH}/tpl/systemd-journald.conf /etc/systemd/journald.conf
systemctl restart systemd-journald

cp ${ROOT_PATH}/tpl/logrotate.nginx.conf /etc/logrotate.d/miz_nginx

#security
cp ${ROOT_PATH}/tpl/fail2ban.conf /etc/fail2ban/jail.local
sudo systemctl restart fail2ban

# nginx
sudo systemctl stop nginx

rm -f /etc/nginx/sites-available/*
rm -f /etc/nginx/sites-enabled/*

#ssl
echo -e "#!/bin/bash\ncertbot renew --quiet" | sudo tee /etc/cron.daily/certbot-renew > /dev/null
sudo chmod +x /etc/cron.daily/certbot-renew

# php config
for f in $(php_list); do
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/cli/conf.d/99-ngatngay.ini
    cp $ROOT_PATH/tpl/php.ini /etc/php/${f}/fpm/conf.d/99-ngatngay.ini
done

# php pool
for p in $(php_list); do
    pool_dir=/etc/php/${p}/fpm/pool.d
    
    rm -f ${pool_dir}/*

    export tpl_php_version=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

# php pool admin
#envsubst < ${ROOT_PATH}/tpl/php-fpm-admin.conf > /etc/php/${PHP_DEFAULT}/fpm/pool.d/admin.conf

# for domain
dir="/www/miz_data/domain"
chown -R www-data:www-data $dir

for d in $dir/*; do
    source $d/config.sh
    export tpl_domain="${tpl_domains%% *}"
    tpls=$(printf '${%s} ' $(env | grep '^tpl_' | cut -d= -f1))

    # nginx
    envsubst "$tpls" < $ROOT_PATH/tpl/nginx_vhost.conf > $d/nginx.conf

    if [ "$tpl_php" = "0" ]; then
        sed -i '/^#php_start$/,/^#php_end$/d' "$d/nginx.conf"
    fi
    if [ -f "$d/nginx.rewrite.conf" ]; then
        python3 $ROOT_PATH/miz_tpl_replace.py $d/nginx.conf $d/nginx.rewrite.conf $d/nginx.conf
    fi

    nginx_vhost_add ${tpl_domain}.conf $d/nginx.conf
  
    #ssl  
    if [[ "$confirm_ssl" == "y" ]]; then
        IFS=' ' read -r -a domain_list <<< "$tpl_domains"
        
        if [[ -f "$d/certbot_dns_cloudflare.ini" ]]; then
            chmod 600 $d/certbot_dns_cloudflare.ini
            certbot certonly --agree-tos --quiet --non-interactive --dns-cloudflare --dns-cloudflare-credentials $d/certbot_dns_cloudflare.ini $(printf -- '-d %s ' "${domain_list[@]}")
        else
            certbot certonly --standalone --agree-tos --quiet --non-interactive $(printf -- '-d %s ' "${domain_list[@]}")
        fi
    fi

    # php
    envsubst "$tpls" < $ROOT_PATH/tpl/php-fpm.conf > $d/php-fpm.conf 
    if [ "$tpl_php" != "0" ]; then
        cp $d/php-fpm.conf /etc/php/${tpl_php}/fpm/pool.d/${tpl_domain}.conf
    fi
done

#php
for p in $(php_list); do
    systemctl stop php${p}-fpm
done
for p in $(php_list); do
    systemctl start php${p}-fpm
done

# nginx

cp ${ROOT_PATH}/tpl/nginx.conf /etc/nginx/nginx.conf

nginx_vhost_add admin ${ROOT_PATH}/tpl/nginx_vhost_admin.conf
nginx_vhost_add default ${ROOT_PATH}/tpl/nginx_vhost_default.conf
nginx -t || exit 1

systemctl start nginx

# finish
echo "---"
echo "updated"
