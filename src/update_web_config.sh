echo "updating..."

# setting ssl
read -p "Do you update SSL? [y/N]: " confirm_ssl
confirm_ssl="${confirm_ssl,,}"

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

# websv

# apache
sudo systemctl stop apache2
rm -f /etc/apache2/sites-available/*

# nginx
#sudo systemctl stop nginx
#rm -f /etc/nginx/sites-available/*
#rm -f /etc/nginx/sites-enabled/*

# php
for p in $(php_list); do
    #stop service
    systemctl stop php${p}-fpm

    # config
    cptpl php-cli.ini /etc/php/${p}/cli/conf.d/99-ngatngay.ini
    cptpl php.ini /etc/php/${p}/fpm/conf.d/99-ngatngay.ini

    #pool
    pool_dir=/etc/php/${p}/fpm/pool.d
    rm -f ${pool_dir}/*

    export tpl_php=$p
    envsubst < ${ROOT_PATH}/tpl/php-fpm-default.conf > $pool_dir/www.conf
done

# php pool admin
#envsubst < ${ROOT_PATH}/tpl/php-fpm-admin.conf > /etc/php/${PHP_DEFAULT}/fpm/pool.d/admin.conf

# for domain
dir="/www/miz_data/domain"
chown -R www-data:www-data $dir

for d in $dir/*; do
    dos2unix -q $d/*
    source $d/config.sh
    export tpl_domain="${tpl_domains%% *}"
    export tpl_domain_dir="/www/web/${tpl_domain}"
    tpls=$(printf '${%s} ' $(env | grep '^tpl_' | cut -d= -f1))
    
    #pre
    mkdir -p $tpl_domain_dir
    mkdir -p $tpl_dir
    
    # apache
    envsubst "$tpls" < $ROOT_PATH/tpl/apache_vhost.conf > $d/apache.conf
    
    if [ "$tpl_php" == "0" ]; then
        sed -i '/^#php_start$/,/^#php_end$/d' "$d/apache.conf"
    fi
    
    cp $d/apache.conf /etc/apache2/sites-available/${tpl_domain}.conf

    #python3 $ROOT_PATH/miz_gen_host.py
    #exit

    # nginx
    #envsubst "$tpls" < $ROOT_PATH/tpl/nginx_vhost.conf > $d/nginx.conf
    #
    #if [ "$tpl_php" == "0" ]; then
    #    sed -i '/^#php_start$/,/^#php_end$/d' "$d/nginx.conf"
    #fi
    #if [ -f "$d/nginx.rewrite.conf" ]; then
    #    python3 $ROOT_PATH/miz_tpl_replace.py $d/nginx.conf $d/nginx.rewrite.conf $d/nginx.conf
    #fi

    #nginx_vhost_add ${tpl_domain}.conf $d/nginx.conf
  
    # php
    if [ "$tpl_php" != "0" ]; then
        export tpl_dir=$(echo "$tpl_dir" | cut -d'/' -f1-4)
        cptpl php-fpm.conf $d/php-fpm-tpl.conf 
        envsubst "$tpls" < $d/php-fpm-tpl.conf > $d/php-fpm.conf 
        cp $d/php-fpm.conf /etc/php/${tpl_php}/fpm/pool.d/${tpl_domain}.conf
    fi
    
    #ssl  
    if [[ "$confirm_ssl" == "y" ]]; then
            IFS=' ' read -r -a domain_list <<< "$tpl_domains"

            if [[ -f "$d/certbot_dns_cloudflare.ini" ]]; then
                chmod 600 $d/certbot_dns_cloudflare.ini
                certbot certonly --agree-tos --quiet --non-interactive --dns-cloudflare --dns-cloudflare-credentials $d/certbot_dns_cloudflare.ini $(printf -- '-d %s ' "${domain_list[@]}")
            else
                certbot certonly --standalone --agree-tos --quiet --non-interactive $(printf -- '-d %s ' "${domain_list[@]}")
            fi
            continue
            
        declare -A groups

        for domain in $tpl_domains; do
            # Tách domain gốc: 2 phần cuối (ví dụ: domain1.com)
            root=$(echo "$domain" | awk -F. '{print $(NF-1)"."$NF}')
            groups["$root"]+="$domain "
        done
        echo "'${!groups[@]}'"
        # In kết quả
        for root in "${!groups[@]}"; do
            #echo "Group: $root"
            echo "Domains: ${groups[$root]}"
            
            IFS=' ' read -r -a domain_list <<< "$(echo "${groups[$root]}" | xargs)"
                  
            if [[ -f "$d/certbot_dns_cloudflare.ini" ]]; then
                chmod 600 $d/certbot_dns_cloudflare.ini
                certbot certonly --agree-tos --quiet --non-interactive --dns-cloudflare --dns-cloudflare-credentials $d/certbot_dns_cloudflare.ini $(printf -- '-d %s ' "${domain_list[@]}")
            else
                certbot certonly --standalone --agree-tos --quiet --non-interactive $(printf -- '-d %s ' "${domain_list[@]}")
            fi
        done
    fi
done

#php
for p in $(php_list); do
    systemctl start php${p}-fpm
done

# apache
a2enmod http2 ssl rewrite headers proxy proxy_http proxy_fcgi setenvif ratelimit 1>/dev/null

cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/zzz_ngatngay.conf
a2disconf '*' 1>/dev/null
a2enconf zzz_ngatngay 1>/dev/null

cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/

a2dissite '*' 1>/dev/null
a2ensite '*' 1>/dev/null

apachectl configtest && systemctl start apache2

# nginx
#cp ${ROOT_PATH}/tpl/nginx.conf /etc/nginx/nginx.conf

#nginx_vhost_add admin ${ROOT_PATH}/tpl/nginx_vhost_admin.conf
#nginx_vhost_add default ${ROOT_PATH}/tpl/nginx_vhost_default.conf
#nginx -t || exit 1

#systemctl start nginx

# finish
echo "---"
echo "updated"
