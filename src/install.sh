if installed; then
    echo "---"
    echo "script installed, only update"
    
    # confirm update 
    echo "---"
fi

# common tool
apt-get update -y
apt-get install -y sudo dos2unix cron logrotate goaccess neovim git fish restic tmux ssl-cert fail2ban software-properties-common vsftpd jq zoxide zip unzip direnv python3-pip python3-full python-is-python3

sudo update-alternatives --install /usr/bin/vi vi /usr/bin/nvim 60 && sudo update-alternatives --set vi /usr/bin/nvim

# mariadb 10.11
apt-get install -y mariadb-server mariadb-backup

# apache
add-apt-repository -y ppa:ondrej/apache2
apt-get install -y apache2

# php
add-apt-repository -y ppa:ondrej/php
php_install 5.6 7.4 8.0 8.1 8.2 8.3 8.4
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
    pma_version="5.2.3"
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
pip install ps_mem --break-system-packages

bash -c 'miz fix' || true

echo 1 > $INSTALLED_FILE

echo
echo
echo "cai dat / cap nhat thanh cong!"
