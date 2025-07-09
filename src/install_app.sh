cd /www/app

if [ ! -d '/www/app/file-manager' ]; then
    mkdir file-manager
    cd file-manager

    curl -O -L https://github.com/ngatngay/file-manager/releases/latest/download/file-manager.zip
    unzip file-manager.zip
fi

if [ ! -d '/www/app/phpmyadmin' ]; then
    PMA_VERSION="5.2.2"
    PMA_NAME="phpMyAdmin-${PMA_VERSION}-english"
    PMA_LINK="https://files.phpmyadmin.net/phpMyAdmin/${PMA_VERSION}/${PMA_NAME}.zip"
    
    curl -O -L $PMA_LINK
    unzip "${PMA_NAME}.zip"
    mv $PMA_NAME phpmyadmin
    rm "${PMA_NAME}.zip"
fi

exit

ip=$(curl -s ipinfo.io/ip)
while true; do
    port=$(( RANDOM % 64512 + 1024 ))
    if ! ss -tuln | grep -q ":$port\b"; then
        break
    fi
done

echo "http://$ip:$port"
efw =

cd /www/app/file-manager
PHP_CLI_SERVER_WORKERS=4 php -S 0.0.0.0:$port
