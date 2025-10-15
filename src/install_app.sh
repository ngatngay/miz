#!/usr/bin/env bash
set -e

app_dir="/www/app"
cd "$app_dir"

# ==== file-manager ====
fm_dir="$app_dir/file-manager"
fm_tmp="/tmp/file-manager.zip"
fm_src="https://static.ngatngay.net/php/file-manager/release.zip"

mkdir -p "$fm_dir"
curl -L -o "$fm_tmp" "$fm_src"
unzip -o "$fm_tmp" -d "$fm_dir"

# ==== phpMyAdmin ====
pma_version="5.2.2"
pma_name="phpMyAdmin-${pma_version}-english"
pma_link="https://files.phpmyadmin.net/phpMyAdmin/${pma_version}/${pma_name}.zip"

pma_dir="$app_dir/phpmyadmin"
pma_zip_tmp="/tmp/phpmyadmin.zip"
pma_extract_tmp="/tmp/pma_extract"

mkdir -p "$pma_dir"
curl -L -o "$pma_zip_tmp" "$pma_link"
rm -rf "$pma_extract_tmp"
mkdir -p "$pma_extract_tmp"
unzip -o "$pma_zip_tmp" -d "$pma_extract_tmp"
cp -a "$pma_extract_tmp/$pma_name/." "$pma_dir/"
