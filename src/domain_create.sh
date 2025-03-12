#!/bin/bash

default_php="8.3"

echo -n "Domain: "
read -e domain

tmp_path="/srv/${domain}"

echo -n "Path (${tmp_path}): "
read -e domain_path

if [ -z "$domain_path" ]; then
    domain_path=$tmp_path
fi

echo -n "PHP Version (default 8.3, 0 to disable): "
read -e php

echo "<VirtualHost *:80>
    ServerName ${domain}
</VirtualHost>

<VirtualHost *:443>
    ServerName ${domain}

    {%~ if d.alias %}
    ServerAlias {{ d.alias | join(' ') }}
    {% endif ~%}

    DocumentRoot /srv/${domain}
    SSLEngine on

    {%~ if d.php %}
    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php{{ d.php }}-fpm.sock|fcgi://localhost"
    </FilesMatch>
    {% endif %}

    SetEnvIf Authorization .+ HTTP_AUTHORIZATION=\$0
</VirtualHost>" > /etc/apache2/sites-available/${domain}.conf

a2ensite $domain
systemctl reload apache2
