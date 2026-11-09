#!/usr/bin/env bash

source /miz/miz.sh

export M_maxclients=10000
export M_backlog=511

apt-get update
apt-get install --yes --no-upgrade valkey-server

envsubst < /miz/tpl/redis.conf > /etc/valkey/valkey.conf
systemctl restart valkey

