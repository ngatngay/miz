#!/bin/bash
set -euo pipefail

apt-get update -y > /dev/null
apt-get install -y fish git tmux > /dev/null

mkdir -p /www
mkdir -p /www/app
mkdir -p /www/data
mkdir -p /www/data/domain
mkdir -p /www/tool
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
cp /www/miz/tpl/bash /etc/profile.d/miz.sh

# for fish
cp /www/miz/tpl/fish /etc/fish/conf.d/miz.fish

echo 'installed /www/miz'
echo
echo 'run: export PATH="/www/miz:$PATH"'