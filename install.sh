#!/bin/bash
set -euo pipefail

rm -rf /www/miz

mkdir -p /www
mkdir -p /www/miz
mkdir -p /www/data
mkdir -p /www/app
mkdir -p /www/log
mkdir -p /www/log/apache
mkdir -p /www/log/apache_html
mkdir -p /www/log/php_fpm
mkdir -p /www/tool
mkdir -p /www/web
mkdir -p /www/backup

apt-get update -y > /dev/null
apt-get install -y fish git tmux > /dev/null

cd /www/miz
curl -L https://static.ngatngay.net/app/miz/miz.tar.gz -o miz.tar.gz
tar -xvf miz.tar.gz

# init shell
sudo cp -r /www/miz/tpl/fish/* /etc/fish/
chsh -s /usr/bin/fish root

echo 'installed /www/miz'

if [ ! -f '/www/data/installed' ]; then
    echo 'logout and re-login'
else
    echo 'installed'
fi
