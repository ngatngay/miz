if installed; then
    echo "---"
    echo "script installed, only update"
    echo "---"
fi

apt-get update -y > /dev/null
apt-get install sudo dos2unix cron logrotate goaccess neovim git fish restic ssl-cert -y > /dev/null

restic self-update > /dev/null

# mariadb
if ! cmd_exists mariadb; then
    curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash -s -- --mariadb-server-version="mariadb-11.4"
    sudo apt-get install mariadb-server
fi

# php
if ! installed; then
    php_install $PHP_DEFAULT
fi

# nginx
if ! installed; then
    echo
fi

#cerbot
if ! cmd_exists certbot; then
    sudo apt install python3 python3-venv libaugeas-dev

    sudo python3 -m venv /opt/certbot/
    sudo /opt/certbot/bin/pip install --upgrade pip

    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-nginx certbot-dns-cloudflare

    sudo ln -s /opt/certbot/bin/certbot /usr/bin/certbot
else
    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-nginx certbot-dns-cloudflare > /dev/null
fi

# memcached
if ! cmd_exists memcached; then
(
    version="1.6.38"
    
    apt-get install autotools-dev automake libevent-dev
    
    cd /opt/
    curl -L -o memcached-${version}.tar.gz https://memcached.org/files/memcached-${version}.tar.gz
    
    tar -zxvf memcached-${version}.tar.gz
    cd memcached-${version}
    
    ./configure && make && sudo make install
    
    # service
    export PORT=11211
    export USER=www-data
    export CACHESIZE=1024
    export MAXCONN=1024
    export OPTIONS=""
    
    envsubst < ${ROOT_PATH}/tpl/memcached.service > /etc/systemd/system/memcached.service
    
    sudo systemctl daemon-reload
    sudo systemctl enable memcached
    sudo systemctl start memcached
    
    efw =
    echo installed memcached-${version}
    memcached --version
)
fi

echo 1 > $INSTALLED_FILE

echo
echo "cai dat / cap nhat thanh cong!"
