#!/usr/bin/bash

echo "PHP Installed"
echo "---"

for f in $(list_php); do
    echo $f
done