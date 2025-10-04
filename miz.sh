#!/bin/bash

INSTALLED_FILE="/www/data/installed"
PHP_DEFAULT="8.3"

installed() {
    [[ -f $INSTALLED_FILE ]]
}

cmd_exists() {
    command -v "$1" >/dev/null 2>&1
}

efw() {
    local char="${1:--}"
    printf '%*s\n' "$(tput cols)" '' | tr ' ' "$char"
}

ehw() {
    local char="${1:--}"
    printf '%*s\n' "$(( $(tput cols) / 2 ))" '' | tr ' ' "$char"
}

cptpl() {
    local input="$1"
    local output="$2"
    local file="${ROOT_PATH}/tpl/${input}"

    if [ -f "${ROOT_PATH}/tpl_overwrite/${input}" ]; then
        file="${ROOT_PATH}/tpl_overwrite/${input}"
    fi
    
    cp -f $file $output
}

rpct() {
  local search="$1" insert_file="$2" target_file="$3"
  # escape cho sed
  local pat
  pat=$(printf '%s' "$search" | sed 's/[.[\*^$\\/&]/\\&/g')
  sed -i -e "/$pat/ r $insert_file" -e "/$pat/ d" "$target_file"
}

#!/bin/bash

domain_load_conf() {
    local json_file="$1"
    jq -r 'to_entries[] | "\(.key)=\(.value|tostring)"' "$json_file" | while IFS="=" read -r key value; do
        var_name="tpl_${key}"
        export "$var_name=$value"
    done
}

domain_valid() {
    local domain="$1"
    local regex="^([a-zA-Z0-9][-a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$"

    if [[ "$domain" =~ $regex ]]; then
        return 0
    else
        return 1
    fi
}


php_list() {
    if [ -d "/etc/php/" ]; then
        php_versions=()

        for version in /etc/php/*; do
            if [ -d "$version" ]; then
                php_versions+=("$(basename "$version")")
            fi
        done

        if [ ${#php_versions[@]} -gt 0 ]; then
            echo "${php_versions[@]}"
        fi
    fi
}

php_install() {
    local version="$1"

    if [[ -z "$version" ]]; then
        echo "Usage: install_php <php_version>"
        return 1
    fi

    local packages=(
        "php${version}"
        "php${version}-apcu"
        "php${version}-bcmath"
        "php${version}-memcached"
        "php${version}-redis"
        "php${version}-cli"
        "php${version}-curl"
        "php${version}-fpm"
        "php${version}-intl"
        "php${version}-gd"
        "php${version}-mbstring"
        "php${version}-mysql"
        "php${version}-sqlite3"
        "php${version}-xml"
        "php${version}-zip"
    )
    
    sudo apt-get update > /dev/null
    sudo apt install -y "${packages[@]}"
}

php_default() {
    local ver="$1"
    local bin="/usr/bin/php${ver}"
    local phpize="/usr/bin/phpize${ver}"
    local phpconfig="/usr/bin/php-config${ver}"

    if [ ! -x "$bin" ]; then
        echo "❌ PHP $ver chưa được cài ở $bin"
        return 1
    fi

    echo "🔧 Cấu hình PHP $ver làm mặc định..."

    sudo update-alternatives --install /usr/bin/php php "$bin" 100
    sudo update-alternatives --set php "$bin"

    if [ -x "$phpize" ]; then
        sudo update-alternatives --install /usr/bin/phpize phpize "$phpize" 100
        sudo update-alternatives --set phpize "$phpize"
    fi

    if [ -x "$phpconfig" ]; then
        sudo update-alternatives --install /usr/bin/php-config php-config "$phpconfig" 100
        sudo update-alternatives --set php-config "$phpconfig"
    fi

    echo "✅ PHP mặc định hiện tại:"
    php -v | head -n 1
}