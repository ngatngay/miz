#!/usr/bin/env bash

echo nooooo
exit

version="1.6.40"

install_memcached=false

if command -v memcached >/dev/null 2>&1; then
    echo "Memcached đã được cài đặt."
    memcached --version

    read -r -p "Bạn có muốn reinstall không? (y/N): " reinstall_choice
    reinstall_choice=${reinstall_choice:-N}

    if [ "$reinstall_choice" = "y" ] || [ "$reinstall_choice" = "Y" ]; then
        install_memcached=true
    else
        echo "Bỏ qua memcached."
    fi
else
    install_memcached=true
fi

if [ "$install_memcached" = true ]; then
    apt-get install --yes --no-upgrade build-essential autotools-dev automake libevent-dev

    cd /opt/ || exit 1
    curl -L -o memcached-${version}.tar.gz https://memcached.org/files/memcached-${version}.tar.gz

    tar -zxvf memcached-${version}.tar.gz
    cd memcached-${version} || exit 1

    ./configure && make && sudo make install
fi

# Hỏi về MAXCONN và CACHESIZE
echo "Enter MAXCONN value (default is 1024):"
read user_maxconn
MAXCONN="${user_maxconn:-1024}"  # Nếu người dùng không nhập gì, mặc định là 1024

echo "Enter CACHESIZE value in MB (default is 1024):"
read user_cachesize
CACHESIZE="${user_cachesize:-1024}"  # Nếu người dùng không nhập gì, mặc định là 1024

# memcached service
export PORT=11211
export USER=www-data
export CACHESIZE
export MAXCONN
export OPTIONS=""

envsubst < /miz/tpl/memcached.service > /etc/systemd/system/memcached.service

sudo systemctl daemon-reload
sudo systemctl enable memcached
sudo systemctl restart memcached

echo '========'
memcached --version
