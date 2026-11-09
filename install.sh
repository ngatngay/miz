#!/usr/bin/env bash
set -euo pipefail

# >> Kiểm tra hệ thống

if [ "$(. /etc/os-release && echo "$ID-$VERSION_ID")" != "ubuntu-26.04" ]; then
    echo "Require Ubuntu 26.04 LTS" >&2
    exit 1
fi

# <<


# >> thông báo bắt đầu

echo 'installing...'
echo

# <<


# >> tạo thư mục cần thiết

mkdir -p /miz-data
mkdir -p /miz-data/app
mkdir -p /miz-data/domains

if [ ! -e /miz-data/settings.json ] && [ ! -d /miz-data/settings ]; then
    printf '{}\n' > /miz-data/settings.json
fi

mkdir -p /var/log/apache2-stats

# <<


# >> cài công cụ bắt buộc

apt-get update --yes
apt-get install --yes --no-upgrade \
    sudo software-properties-common cron logrotate rsyslog dos2unix e2fsprogs \
    lsb-release ca-certificates curl \
    db-util \
    curl git tmux fish neovim zoxide ripgrep jq strace direnv zip unzip socat netcat-openbsd \
    python3 python3-dev python3-venv python3-pip python3-full python-is-python3 \
    gcc libaugeas-dev \
    fail2ban \
    libarchive-tools p7zip-full unzip git wget shellcheck

pip install ps_mem --break-system-packages

# <<


# >> tạo user

if id www &>/dev/null; then
    usermod -s /usr/bin/fish www
else
    useradd -m -s /usr/bin/fish www
fi

if id miz-admin &>/dev/null; then
    usermod -s /usr/sbin/nologin miz-admin
else
    useradd -M -s /usr/sbin/nologin miz-admin
fi

usermod -aG www,miz-admin www-data

# <<


# >> tối ưu kernel

cp -f /miz/tpl/sysctl.conf /etc/sysctl.d/99-zzz.conf
sysctl --system

# <<

# >> bảo mật hệ thống

(
    cp -r /miz/tpl/fail2ban.action/* /etc/fail2ban/action.d/
    cp -r /miz/tpl/fail2ban.filter/* /etc/fail2ban/filter.d/
    cp -f /miz/tpl/fail2ban.conf /etc/fail2ban/jail.local
    systemctl restart fail2ban
)

# <<

# >> giới hạn nhật ký

cp -f /miz/tpl/systemd-journald.conf /etc/systemd/journald.conf
systemctl restart systemd-journald

# <<


# >> cấu hình shell
cp -r /miz/tpl/fish/* /etc/fish/
chsh -s /usr/bin/fish root

sudo update-alternatives --install /usr/bin/vi vi /usr/bin/nvim 60
sudo update-alternatives --set vi /usr/bin/nvim

# <<


# >> tải công cụ tiện ích hỗ trợ

/miz/bin/miz-download-bin composer https://static.nightmare.top/app/composer/composer
/miz/bin/miz-download-bin phpstan https://static.nightmare.top/app/phpstan/phpstan
/miz/bin/miz-download-bin php-cs-fixer https://static.nightmare.top/app/php-cs-fixer/php-cs-fixer
/miz/bin/miz-download-bin wp https://static.nightmare.top/app/wp/wp

/miz/bin/miz-download-file /miz-data/app/file-manager-php.zip https://static.nightmare.top/app/file-manager-php/release.zip
/miz/bin/miz-download-file /miz-data/app/phpmyadmin.zip https://files.phpmyadmin.net/snapshots/phpMyAdmin-5.2+snapshot-english.zip

# <<


# >> phân quyền bảo mật

chmod +x /miz/bin/*

chmod 750 /miz
chown miz-admin:miz-admin /miz

chmod 750 /miz-data
chown miz-admin:miz-admin /miz-data

# <<


# >> DONE

echo
echo 'installed.'

# <<
