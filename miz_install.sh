#!/bin/bash
set -euo pipefail

apt update
apt install git -y

mkdir -p /www
mkdir -p /www/miz_app
mkdir -p /www/miz_data
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
    echo 'set -gx PATH /www/miz $PATH' | sudo tee /etc/fish/conf.d/miz.fish > /dev/null
fi

echo 'installed /www/miz'
echo
echo 'run: export PATH="/www/miz:$PATH"'