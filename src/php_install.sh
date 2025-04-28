#!/bin/bash

source $ROOT_PATH/src/php_list.sh

echo
read -p "Nhập phiên bản PHP cần cài: " version

php_install "$version"

echo
echo "chay lai lenh update de hoan tat cai dat"
