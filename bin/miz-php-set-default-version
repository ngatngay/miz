#!/usr/bin/env bash
set -euo pipefail

ver="$1"
bin="/usr/bin/php${ver}"
phpize="/usr/bin/phpize${ver}"
phpconfig="/usr/bin/php-config${ver}"

if [ ! -x "$bin" ]; then
    echo "❌ PHP $ver chưa được cài ở $bin"
    exit 1
fi

echo "🔧 Cấu hình PHP $ver làm mặc định..."

update-alternatives --install /usr/bin/php php "$bin" 100
update-alternatives --set php "$bin"

if [ -x "$phpize" ]; then
    update-alternatives --install /usr/bin/phpize phpize "$phpize" 100
    update-alternatives --set phpize "$phpize"
fi

if [ -x "$phpconfig" ]; then
    update-alternatives --install /usr/bin/php-config php-config "$phpconfig" 100
    update-alternatives --set php-config "$phpconfig"
fi

echo "✅ PHP mặc định hiện tại:"
php -v | head -n 1
