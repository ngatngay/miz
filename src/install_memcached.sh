# memcached
version="1.6.39"

if cmd_exists memcached; then
    efw =
    echo 'installed'
    memcached --version
    exit
fi

(
    apt-get install -y build-essential autotools-dev automake libevent-dev
    
    cd /opt/
    curl -L -o memcached-${version}.tar.gz https://memcached.org/files/memcached-${version}.tar.gz
    
    tar -zxvf memcached-${version}.tar.gz
    cd memcached-${version}
    
    ./configure && make && sudo make install
)

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

envsubst < ${ROOT_PATH}/tpl/memcached.service > /etc/systemd/system/memcached.service

sudo systemctl daemon-reload
sudo systemctl enable memcached
sudo systemctl restart memcached

efw =
memcached --version
