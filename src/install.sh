phps=("7.4" "8.3")

if installed; then
    echo "---"
    echo "script installed, only update"
    echo "---"
fi

apt update
apt upgrade -y

apt install neovim git fish rclone restic

# init
restic self-update

# mariadb
if ! installed; then
    sudo apt-get install apt-transport-https curl
    sudo mkdir -p /etc/apt/keyrings
    sudo curl -o /etc/apt/keyrings/mariadb-keyring.pgp 'https://mariadb.org/mariadb_release_signing_key.pgp'

    cat << 'EOF' > /etc/apt/sources.list.d/mariadb.sources
# MariaDB 10.11 repository list - created 2025-03-12 01:45 UTC
# https://mariadb.org/download/
X-Repolib-Name: MariaDB
Types: deb
# deb.mariadb.org is a dynamic mirror if your preferred mirror goes offline. See https://mariadb.org/mirrorbits/ for details.
# URIs: https://deb.mariadb.org/10.11/debian
URIs: https://vn-mirrors.vhost.vn/mariadb/repo/10.11/debian
Suites: bookworm
Components: main
Signed-By: /etc/apt/keyrings/mariadb-keyring.pgp
EOF

    sudo apt-get update
    sudo apt-get install mariadb-server
fi

# php
if ! installed; then
    for i in "${phps[@]}"; do
        php_install $i
    done
fi

# apahce
if ! installed; then
    echo
fi

echo 1 > $INSTALLED_FILE

echo
echo "cai dat / cap nhat thanh cong!"
