#!/usr/bin/env bash

set +e
set +u
set +o pipefail

EXT="*.log"

file_count=0
total_size=0

LOG_DIRS=(
    /var/log/apache2
    /var/log/nginx
    /var/log/mysql
)

echo "cleaning..."

for LOG_DIR in "${LOG_DIRS[@]}"; do
    [ -d "$LOG_DIR" ] || continue

    while IFS= read -r -d '' file; do
        ((file_count++))

        size=$(stat -c%s "$file" 2>/dev/null || echo 0)
        total_size=$((total_size + size))

        truncate -s 0 "$file"
    done < <(find "$LOG_DIR" -type f -name "$EXT" -print0 2>/dev/null || true)
done

while IFS= read -r -d '' file; do
    ((file_count++))

    size=$(stat -c%s "$file" 2>/dev/null || echo 0)
    total_size=$((total_size + size))

    truncate -s 0 "$file"
done < <(find /var/log -maxdepth 1 -type f -name 'php*-fpm.log' -print0 2>/dev/null || true)

echo "----"
echo "file: $file_count"
echo "size: $((total_size / 1024 / 1024)) MB"
echo --
echo "size (system): $(du -sh /var/log 2>/dev/null | awk '{print $1}')"
