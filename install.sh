#!/bin/bash
set -euo pipefail

apt-get update -y > /dev/null
apt-get install -y fish git tmux > /dev/null

mkdir -p /www

if [ ! -d '/www/miz' ]; then
    cd /www
    git clone --depth 1 https://github.com/ngatngay/miz
fi

git config --global --add safe.directory /www/miz

# add PATH
cp /www/miz/tpl/fish /etc/fish/conf.d/miz.fish
chsh -s /usr/bin/fish root

echo 'installed /www/miz'
echo
echo 'logout and re-login'

