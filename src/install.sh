if installed; then
    echo "---"
    echo "script installed, only update"
    
    # confirm update 
    
    echo "---"
fi

# common tool
apt-get update -y
apt-get install -y sudo dos2unix cron logrotate goaccess neovim git fish restic tmux ssl-cert fail2ban software-properties-common vsftpd

sudo update-alternatives --install /usr/bin/vi vi /usr/bin/nvim 60 && sudo update-alternatives --set vi /usr/bin/nvim

# mariadb 10.11
apt-get install mariadb-server

# apache
add-apt-repository -y ppa:ondrej/apache2

apt-get install apache2

# php
add-apt-repository -y ppa:ondrej/php

php_install 5.6
php_install 7.4
php_install 8.0
php_install 8.1
php_install 8.2
php_install 8.3
php_install 8.4

php_default 8.3

# ssl - cerbot
if ! cmd_exists certbot; then
    sudo apt install python3 python3-venv libaugeas-dev

    sudo python3 -m venv /opt/certbot/
    sudo /opt/certbot/bin/pip install --upgrade pip

    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-dns-cloudflare

    sudo ln -s /opt/certbot/bin/certbot /usr/bin/certbot
else
    sudo /opt/certbot/bin/pip install --upgrade certbot certbot-dns-cloudflare
fi

# memcached
if ! cmd_exists memcached; then
(
    version="1.6.38"
    
    apt-get install build-essential autotools-dev automake libevent-dev
    
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
echo
echo "cai dat / cap nhat thanh cong!"
