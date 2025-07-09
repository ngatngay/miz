#!/bin/bash

HTPASSWD_FILE="/www/data/admin.htpasswd"

# Nhập mật khẩu (ẩn khi gõ)
while true; do
    read -s -p "Nhập mật khẩu: " password
    echo
    read -s -p "Nhập lại mật khẩu: " password_confirm
    echo

    if [[ -z "$password" ]]; then
        echo "❌ Mật khẩu không được để trống."
        continue
    fi

    if [[ ${#password} -lt 8 ]]; then
        echo "❌ Mật khẩu phải dài ít nhất 8 ký tự."
        continue
    fi

    if [[ "$password" != "$password_confirm" ]]; then
        echo "❌ Mật khẩu nhập lại không khớp."
        continue
    fi

    break
done

# Tạo file .htpasswd với user đầu tiên
htpasswd -cb "$HTPASSWD_FILE" "admin" "$password"

echo "✅ Đã tạo file $HTPASSWD_FILE."
echo "✅ Bạn có thể đăng nhập tại: https://[IP]:9869"
