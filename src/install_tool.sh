cd /www/tool

curl -o composer -L https://getcomposer.org/download/latest-stable/composer.phar
chmod +x composer

curl -o phpstan -L https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar
chmod +x phpstan

curl -o php-cs-fixer -L https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/latest/download/php-cs-fixer.phar
chmod +x php-cs-fixer

curl -o wp -L https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp
