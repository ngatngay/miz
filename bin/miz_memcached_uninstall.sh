#!/usr/bin/env bash

set -e

source /miz/miz.sh

SERVICE_FILE="/etc/systemd/system/memcached.service"
INSTALL_PREFIX="/usr/local"
BIN_PATH="${INSTALL_PREFIX}/bin/memcached"

echo "=== Uninstall Memcached ==="
echo
echo "Hành động này sẽ:"
echo "- Stop & disable memcached service"
echo "- Xóa systemd service file"
echo "- Xóa memcached binary (${BIN_PATH})"
echo "- Xóa source build trong /opt"
echo

read -r -p "Bạn có chắc chắn muốn uninstall memcached không? (y/N): " confirm_choice
confirm_choice=${confirm_choice:-N}

if [ "$confirm_choice" != "y" ] && [ "$confirm_choice" != "Y" ]; then
    echo "Hủy uninstall."
    exit 0
fi

echo
echo "Bắt đầu uninstall memcached..."
echo

# Stop & disable service nếu tồn tại
if systemctl list-unit-files | grep -q memcached.service; then
    echo "Stopping memcached service..."
    systemctl stop memcached || true

    echo "Disabling memcached service..."
    systemctl disable memcached || true
else
    echo "memcached service không tồn tại."
fi

# Xóa file service
if [ -f "$SERVICE_FILE" ]; then
    echo "Removing systemd service file..."
    rm -f "$SERVICE_FILE"
fi

# Reload systemd
systemctl daemon-reload

# Xóa binary memcached
if [ -f "$BIN_PATH" ]; then
    echo "Removing memcached binary..."
    rm -f "$BIN_PATH"
else
    echo "memcached binary không tồn tại."
fi

# Xóa các file liên quan khác (nếu có)
for file in \
    "${INSTALL_PREFIX}/share/man/man1/memcached.1" \
    "${INSTALL_PREFIX}/include/memcached" \
    "${INSTALL_PREFIX}/lib/libmemcached"*; do
    [ -e "$file" ] && rm -rf "$file"
done

# Xóa source build
if [ -d /opt ]; then
    echo "Cleaning memcached source in /opt..."
    rm -rf /opt/memcached-*
fi

# Kiểm tra kết quả
echo
if command -v memcached >/dev/null 2>&1; then
    echo "⚠️  Memcached vẫn còn trong hệ thống:"
    memcached --version
else
    echo "✅ Memcached đã được gỡ bỏ hoàn toàn."
fi
