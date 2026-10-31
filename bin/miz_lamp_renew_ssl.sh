#!/usr/bin/env bash

certbot certificates 2>/dev/null | awk '
/Certificate Name:/ { name=$3 }
/Expiry Date:/ && /EXPIRED/ { print name }
' | while read -r name; do
    certbot delete --cert-name "$name" --non-interactive
done

certbot renew -n -q

php /miz/bin/miz_lamp_commit_ssl.sh
systemctl reload haproxy
