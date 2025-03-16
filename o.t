<FilesMatch "\.php$"> SetHandler "proxy:unix:/run/php/php8.3-fpm-1.com.sock|fcgi://localhost" </FilesMatch>
