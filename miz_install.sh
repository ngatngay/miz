#!/bin/bash
set -euo pipefail

apt-get update -y > /dev/null
apt-get install git -y > /dev/null

mkdir -p /www
mkdir -p /www/miz_app
mkdir -p /www/miz_data
mkdir -p /www/miz_tool
mkdir -p /www/log
mkdir -p /www/log/apache
mkdir -p /www/log/apache_html
mkdir -p /www/web

if [ ! -d '/www/miz' ]; then
    cd /www
    git clone --depth 1 https://github.com/ngatngay/miz
fi

# add PATH

# for bash
echo 'export PATH="/www/miz:$PATH"' | sudo tee /etc/profile.d/miz.sh > /dev/null

# for fish
if [ -d /etc/fish/conf.d ]; then
    cp /www/miz/tpl/fish /etc/fish/conf.d/miz.fish
fi

echo 'installed /www/miz'
echo
echo 'run: export PATH="/www/miz:$PATH"'