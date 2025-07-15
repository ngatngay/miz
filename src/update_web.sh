echo "updating web..."

# setting ssl
read -p "Do you update SSL? [y/N]: " confirm_ssl
confirm_ssl="${confirm_ssl,,}"

# apache
sudo systemctl stop apache2
rm -f /etc/apache2/sites-available/*

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
dir="/www/data/domain"
chown -R www-data:www-data $dir

for d in $dir/*; do
    dos2unix -q $d/*
    source $d/config.sh
    export tpl_domain="${tpl_domains%% *}"
    export tpl_domain_dir="/www/web/${tpl_domain}"
    tpls=$(printf '${%s} ' $(env | grep '^tpl_' | cut -d= -f1))
    
    #info
    echo -- $tpl_domain
    
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
            ssl_failed=0

            if [[ -f "$d/certbot_dns_cloudflare.ini" ]]; then
                chmod 600 $d/certbot_dns_cloudflare.ini
                certbot certonly --agree-tos --quiet --non-interactive --expand --dns-cloudflare --dns-cloudflare-credentials $d/certbot_dns_cloudflare.ini $(printf -- '-d %s ' "${domain_list[@]}") || ssl_failed=1
            else
                certbot certonly --standalone --agree-tos --quiet --non-interactive --expand $(printf -- '-d %s ' "${domain_list[@]}") || ssl_failed=1
            fi
            
            if [[ $ssl_failed -eq 1 ]]; then
                #err
                sed -i '/^#ssl_file_start$/,/^#ssl_file_end$/d' "$d/apache.conf"
                echo "- Không lấy được ssl"
            else
                #done
                sed -i '/^#ssl_file_def_start$/,/^#ssl_file_def_end$/d' "$d/apache.conf"
            fi

            cp $d/apache.conf /etc/apache2/sites-available/${tpl_domain}.conf
    fi
done

#php
for p in $(php_list); do
    systemctl start php${p}-fpm
done

# apache
cp ${ROOT_PATH}/tpl/apache.conf /etc/apache2/conf-available/zzz_ngatngay.conf
a2disconf '*' 1>/dev/null
a2enconf zzz_ngatngay 1>/dev/null

cp ${ROOT_PATH}/tpl/apache_vhost_default.conf /etc/apache2/sites-available/

a2dissite '*' >/dev/null
a2ensite '*' >/dev/null

apachectl configtest && systemctl start apache2

# finish
echo "---"
echo "updated"
