#!/bin/bash

source ./src/php_list.sh

echo
read -p "Nhập phiên bản PHP cần cài: " version

install_php "$version"

echo
echo "chay lai lenh update de hoan tat cai dat"
