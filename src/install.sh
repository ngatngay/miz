if installed; then
    echo "---"
    echo "script installed, only update"
    echo "---"
fi

apt update

# common tool
apt install sudo cron logrotate goaccess neovim git fish restic ssl-cert

# init
restic self-update

# mariadb
if ! installed; then
    curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash -s -- --mariadb-server-version="mariadb-11.4"
    sudo apt-get install mariadb-server
fi

# php
if ! installed; then
    php_install PHP_DEFAULT
fi

# nginx
if ! installed; then
    echo
fi

#cerbot
if ! installed; then
    sudo apt update
    sudo apt install python3 python3-venv libaugeas-dev

    sudo python3 -m venv /opt/certbot/
    sudo /opt/certbot/bin/pip install --upgrade pip

    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-nginx certbot-dns-cloudflare

    sudo ln -s /opt/certbot/bin/certbot /usr/bin/certbot
else
    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-nginx certbot-dns-cloudflare
fi

echo 1 > $INSTALLED_FILE

echo
echo "cai dat / cap nhat thanh cong!"
