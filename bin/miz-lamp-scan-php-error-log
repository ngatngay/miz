#!/usr/bin/env bash

ROOT_DIR="/home/www/"

echo "Scan error_log trong $ROOT_DIR ..."
echo "-----------------------------------------"

result=$(find "$ROOT_DIR" -type f -name "error_log" -exec du -b {} + 2>/dev/null | sort -nr)

# Hiển thị danh sách theo MB
echo "$result" | awk '{
    size_mb = $1 / 1024 / 1024;
    $1="";
    sub(/^ /,"");
    printf "%.2f MB\t%s\n", size_mb, $0
}'

# Tính tổng MB
total_size=$(echo "$result" | awk '{sum+=$1} END {print sum/1024/1024}')

echo "-----------------------------------------"
printf "Tong dung luong: %.2f MB\n" "$total_size"

# Hỏi xoá
echo -n "Ban co muon xoa tat ca file tren? (y/N): "
read -r confirm

if [[ "$confirm" == "y" || "$confirm" == "Y" ]]; then
    echo "Dang xoa..."
    echo "$result" | awk '{ $1=""; sub(/^ /,""); print }' | while read -r file; do
        rm -f "$file"
    done
    echo "Da xoa xong."
else
    echo "done."
fi
