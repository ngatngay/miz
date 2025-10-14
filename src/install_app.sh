#!/usr/bin/env bash
set -e

cd /www/app

# ==== file-manager ====
mkdir -p file-manager
tmp_fm="/tmp/file-manager.zip"
curl -L -o "$tmp_fm" "https://static.ngatngay.net/php/file-manager/release.zip"
unzip -o "$tmp_fm" -d "file-manager"

# ==== phpMyAdmin ====
PMA_VERSION="5.2.2"
PMA_NAME="phpMyAdmin-${PMA_VERSION}-english"
PMA_LINK="https://files.phpmyadmin.net/phpMyAdmin/${PMA_VERSION}/${PMA_NAME}.zip"

mkdir -p phpmyadmin
tmp_pma_zip="/tmp/phpmyadmin.zip"
tmp_pma_dir="/tmp/pma_extract"

curl -L -o "$tmp_pma_zip" "$PMA_LINK"
rm -rf "$tmp_pma_dir"
mkdir -p "$tmp_pma_dir"
unzip -o "$tmp_pma_zip" -d "$tmp_pma_dir"
# chép đè nội dung vào thư mục hiện có
cp -a "$tmp_pma_dir/$PMA_NAME/." "phpmyadmin/"
