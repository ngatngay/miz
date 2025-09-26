if installed; then
    echo "---"
    echo "script installed, only update"
    
    # confirm update 
    
    echo "---"
fi

# common tool
apt-get update -y
apt-get install -y sudo dos2unix cron logrotate goaccess neovim git fish restic tmux ssl-cert fail2ban software-properties-common vsftpd supervisor jq zoxide zip unzip python3-full

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

# supervisor

SUP_CONF_DIR="/www/data/supervisor"
SYSTEMD_FILE="/etc/systemd/system/supervisord-www-data.service"

mkdir -p /www/data/supervisor
mkdir -p /www/data/supervisor/log
mkdir -p /www/data/supervisor/conf.d

# Tạo file systemd service
cat > "$SYSTEMD_FILE" <<'EOF'
[Unit]
Description=Supervisor for www-data
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
ExecStart=/usr/bin/supervisord -n -c /www/data/supervisor/supervisord.conf
Restart=on-failure
KillMode=process

[Install]
WantedBy=multi-user.target
EOF

# Tạo file supervisord.conf cho www-data
cat > "$SUP_CONF_DIR/supervisord.conf" <<'EOF'
[supervisord]
nodaemon=true
logfile=/www/data/supervisor/supervisord.log
childlogdir=/www/data/supervisor/log

[unix_http_server]
file=/www/data/supervisor/supervisor.sock
chmod=0770
chown=www-data:www-data

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix:///www/data/supervisor/supervisor.sock

[include]
files=/www/data/supervisor/conf.d/*.conf
EOF

chown -R www-data:www-data /www/data
systemctl daemon-reload
systemctl enable supervisord-www-data
systemctl stop supervisord-www-data
systemctl start supervisord-www-data

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
memcached_version="1.6.39"
memcached_current_version=""

if cmd_exists memcached; then
    memcached_current_version=$(memcached -V | awk '{print $2}')
fi

if [ "$memcached_current_version" != "$memcached_version" ]; then
(
    version="$memcached_version"
    
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
