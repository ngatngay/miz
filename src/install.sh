if installed; then
    echo "---"
    echo "script installed, only update"
    
    # confirm update 
    echo "---"
fi

# common tool
apt-get update -y
apt-get install -y sudo dos2unix cron logrotate goaccess neovim git fish restic tmux ssl-cert fail2ban software-properties-common vsftpd jq zoxide zip unzip python3-full

sudo update-alternatives --install /usr/bin/vi vi /usr/bin/nvim 60 && sudo update-alternatives --set vi /usr/bin/nvim

# mariadb 10.11
apt-get install -y mariadb-server

# apache
add-apt-repository -y ppa:ondrej/apache2

apt-get install -y apache2

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
    sudo apt install -y python3 python3-venv libaugeas-dev

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
    
    apt-get install -y build-essential autotools-dev automake libevent-dev
    
    cd /opt/
    curl -L -o memcached-${version}.tar.gz https://memcached.org/files/memcached-${version}.tar.gz
    
    tar -zxvf memcached-${version}.tar.gz
    cd memcached-${version}
    
    ./configure && make && sudo make install
)
fi
(
    # memcached service
    export PORT=11211
    export USER=www-data
    export CACHESIZE=1024
    export MAXCONN=1024
    export OPTIONS=""
    
    envsubst < ${ROOT_PATH}/tpl/memcached.service > /etc/systemd/system/memcached.service
    
    sudo systemctl daemon-reload
    sudo systemctl enable memcached
    sudo systemctl restart memcached
    
    efw =
    memcached --version
)

#nodejs
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
node -v

npm install --global corepack
corepack enable pnpm

npm install -g \
    npm-check-updates \
    pm2 \
    nodemon

pm2 install pm2-logrotate
#nodejs end

echo 'install app'
(
    app_dir="/www/app"
    cd "$app_dir"
    
    # ==== file-manager ====
    fm_dir="$app_dir/file-manager"
    fm_tmp="/tmp/file-manager.zip"
    fm_src="https://static.ngatngay.net/php/file-manager/release.zip"
    
    mkdir -p "$fm_dir"
    curl -L -o "$fm_tmp" "$fm_src"
    unzip -o "$fm_tmp" -d "$fm_dir"
    
    # ==== phpMyAdmin ====
    pma_version="5.2.2"
    pma_name="phpMyAdmin-${pma_version}-english"
    pma_link="https://files.phpmyadmin.net/phpMyAdmin/${pma_version}/${pma_name}.zip"
    
    pma_dir="$app_dir/phpmyadmin"
    pma_zip_tmp="/tmp/phpmyadmin.zip"
    pma_extract_tmp="/tmp/pma_extract"
    
    mkdir -p "$pma_dir"
    curl -L -o "$pma_zip_tmp" "$pma_link"
    rm -rf "$pma_extract_tmp"
    mkdir -p "$pma_extract_tmp"
    unzip -o "$pma_zip_tmp" -d "$pma_extract_tmp"
    cp -a "$pma_extract_tmp/$pma_name/." "$pma_dir/"
)

echo 'install tool'
(
    cd /www/tool
    
    curl -o composer -L https://getcomposer.org/download/latest-stable/composer.phar
    chmod +x composer
    
    curl -o phpstan -L https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar
    chmod +x phpstan
    
    curl -o php-cs-fixer -L https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/latest/download/php-cs-fixer.phar
    chmod +x php-cs-fixer
    
    curl -o wp -L https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp
)

bash -c 'miz fix'

echo 1 > $INSTALLED_FILE

echo
echo
echo "cai dat / cap nhat thanh cong!"
