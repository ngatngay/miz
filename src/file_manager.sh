if [ ! -d '/www/miz_app/file-manager' ]; then
    cd /www/miz_app
    mkdir file-manager
    cd file-manager

    curl -O -L https://github.com/ngatngay/file-manager/releases/latest/download/file-manager.zip
    unzip file-manager.zip
fi

ip=$(curl -s ipinfo.io/ip)
while true; do
    port=$(( RANDOM % 64512 + 1024 ))
    if ! ss -tuln | grep -q ":$port\b"; then
        break
    fi
done

echo "http://$ip:$port"
efw =

cd /www/miz_app/file-manager
PHP_CLI_SERVER_WORKERS=4 php -S 0.0.0.0:$port
