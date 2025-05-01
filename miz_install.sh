#!/bin/bash

if [ -e '/opt/miz' ]; then
    echo 'installed!'
    exit
fi

#install
cd /opt
git clone --depth 1 https://github.com/ngatngay/miz

# add PATH
echo 'export PATH="/opt/miz:$PATH"' | sudo tee /etc/profile.d/miz.sh > /dev/null
sudo chmod +x /etc/profile.d/miz.sh

source /etc/profile.d/miz.sh
