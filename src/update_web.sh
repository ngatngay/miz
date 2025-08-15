#!/bin/bash

shopt -s nullglob

echo "updating..."

# domain selection
if [ -n "${2-}" ]; then
    target_domain="${2// /}"  # remove spaces
else
    read -p "nhap domain can cap nhat (bo qua de cap nhat tat ca): " target_domain
    target_domain="${target_domain// /}"
fi

# setting ssl
read -p "Do you update SSL? [y/N]: " confirm_ssl
confirm_ssl="${confirm_ssl,,}"

if [[ "$confirm_ssl" == "y" ]]; then
    certbot_key=/www/data/certbot_dns_cloudflare.ini

    if [[ -f "$certbot_key" ]]; then
        chmod 600 $certbot_key
    else
        echo "chua co file $certbot_key"
        exit
    fi
fi

# for domains
dir="/www/data/domain"
chown -R www-data:www-data $dir

# clean
DATA_DIR=$dir
PATTERNS=(
  "/etc/php"/*/"fpm/pool.d/9-*.conf"
  "/etc/apache2/sites-available/9-*.conf"
)

declare -A KEEP
for d in "$DATA_DIR"/*/ "$DATA_DIR"/*; do
  [[ -d "$d" ]] || continue
  domain="${d%/}"
  domain="${domain##*/}"
  [[ -n "$domain" ]] && KEEP["$domain"]=1
done

if [[ ${#KEEP[@]} -gt 0 ]]; then
  for pat in "${PATTERNS[@]}"; do
    for f in $pat; do
      name="${f##*/}"        # ví dụ: 9-example.com.conf
      name="${name#9-}"      # -> example.com.conf
      domain="${name%.conf}" # -> example.com
      [[ -n "${KEEP[$domain]+x}" ]] || rm -f -- "$f"
    done
  done
fi

# run
for d in $dir/*; do
    dos2unix -q $d/*
    source $d/config.sh
    export tpl_domains="$(echo "$tpl_domains" | xargs)"
    export tpl_domain="${tpl_domains%% *}"
    export tpl_domain_dir="/www/web/${tpl_domain}"
    tpls=$(printf '${%s} ' $(env | grep '^tpl_' | cut -d= -f1))

    # 1 domain
    if [[ -n "$target_domain" && ! " $tpl_domains " =~ " $target_domain " ]]; then
        continue
    fi
    
    #info
    echo -- $tpl_domains
    
    #pre
    find $d -type f ! -name 'config.sh' ! -name 'apache_ssl.conf' -delete 2>/dev/null

    mkdir -p $tpl_domain_dir
    mkdir -p $tpl_dir

    # apache
    envsubst "$tpls" < $ROOT_PATH/tpl/apache_vhost.conf > $d/apache.conf

    # php
    if [ "$tpl_php" == "0" ]; then
        sed -i '/^#php_start$/,/^#php_end$/d' "$d/apache.conf"
    fi
    if [ "$tpl_php" != "0" ]; then
        # delete old
        rm -f /etc/php/*/fpm/pool.d/9-${tpl_domain}.conf

        export tpl_dir=$(echo "$tpl_dir" | cut -d'/' -f1-4)
        cptpl php-fpm.conf $d/php-fpm-tpl.conf 
        envsubst "$tpls" < $d/php-fpm-tpl.conf > $d/php-fpm.conf 
        cp $d/php-fpm.conf /etc/php/${tpl_php}/fpm/pool.d/9-${tpl_domain}.conf
    fi
    
    #ssl
    if [[ "$confirm_ssl" == "y" ]]; then
            IFS=' ' read -r -a domain_list <<< "$tpl_domains"
            ssl_failed=0

            c_args=()
            for c_domain in $tpl_domains; do
                c_args+=(-d "$c_domain")
            done
            
            certbot certonly --agree-tos --quiet --non-interactive --expand \
                --dns-cloudflare --dns-cloudflare-credentials "$certbot_key" \
                --cert-name "$tpl_domain" \
                "${c_args[@]}" || ssl_failed=1
            
            # certbot certonly --standalone --agree-tos --quiet --non-interactive --expand $(printf -- '-d %s ' "${domain_list[@]}") || ssl_failed=1
            
            if [[ $ssl_failed -eq 1 ]]; then
                #err
                echo "- Không lấy được ssl"
                rm -f $d/apache_ssl.conf
            else
                #done
                cat > "$d/apache_ssl.conf" <<EOF
SSLCertificateFile /etc/letsencrypt/live/${tpl_domain}/fullchain.pem
SSLCertificateKeyFile /etc/letsencrypt/live/${tpl_domain}/privkey.pem
EOF
            fi
    fi

    if [ ! -f "$d/apache_ssl.conf" ]; then
        cat > "$d/apache_ssl.conf" <<EOF
SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
EOF
    fi
    rpct '#ssl' $d/apache_ssl.conf $d/apache.conf
    #ssl_end
    
    cp $d/apache.conf /etc/apache2/sites-available/9-${tpl_domain}.conf
    a2ensite 9-${tpl_domain} 1>/dev/null

    if ! apachectl configtest >/dev/null 2>&1; then
        apachectl configtest
    fi
done

for p in $(php_list); do
    systemctl reload php${p}-fpm
done
systemctl reload apache2

# finish
echo "---"
echo "updated"