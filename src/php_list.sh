#!/usr/bin/bash

echo "PHP Installed"
echo "---"

for f in $(php_list); do
    echo $f
done