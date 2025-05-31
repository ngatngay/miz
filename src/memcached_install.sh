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