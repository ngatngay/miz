#!/bin/bash

chown -R www-data:www-data /var/www/ /www

chown -R root:root /www/miz
find /www/miz -type d -exec chmod 711 {} \;
find /www/miz -type f -exec chmod 644 {} \;
chmod +x /www/miz/bin/*

ehw '-'
echo 'fix ok'
