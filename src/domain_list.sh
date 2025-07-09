#!/bin/bash

echo "--- Danh sách domain ---"

DOMAIN_LIST=()

# Lấy danh sách domain hợp lệ và gán số cho từng domain
i=1
for file in /www/data/domain/*; do
    name="$(basename "$file" .conf)"

    skip=false
    for hidden in "${HIDDEN_FILES[@]}"; do
        if [[ "$name" == "$hidden" ]]; then
            skip=true
            break
        fi
    done

    if [[ "$skip" == false ]]; then
        echo "$i. $name"
        DOMAIN_LIST+=("$name")
        ((i++))
    fi
done

echo ""
read -p "Nhập số hoặc tên domain cần chỉnh sửa: " input

# Kiểm tra xem người dùng nhập số hay tên domain
if [[ "$input" =~ ^[0-9]+$ ]]; then
    # Người dùng nhập số
    index=$((input - 1))
    if [[ "$index" -ge 0 && "$index" -lt "${#DOMAIN_LIST[@]}" ]]; then
        domain="${DOMAIN_LIST[$index]}"
        nvim "/www/data/domain/$domain/nginx.conf"
    else
        echo "Số không hợp lệ!"
    fi
else
    # Người dùng nhập tên domain
    domain="$input"
    if [[ " ${DOMAIN_LIST[*]} " =~ " $domain " ]]; then
        nvim "/etc/apache2/sites-available/$domain.conf"
    else
        echo "Domain không hợp lệ!"
    fi
fi
