#!/bin/bash

mkdir -p /www/backup

# cert
echo -- cert

rsync -aAX --delete /etc/letsencrypt/ /www/backup/cert/

# cron
echo -- cron
mkdir -p /www/backup/cron

crontab -l -u root > /www/backup/cron/root.txt || true
crontab -l -u www-data > /www/backup/cron/www-data.txt || true

