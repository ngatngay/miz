    local country

    apt-get install --no-upgrade whois --yes

    country=$(whois miz_get_server_country | grep -i "^country" | head -n1 | awk '{print $2}')
    VPS_COUNTRY="${country:-UNKNOWN}"

