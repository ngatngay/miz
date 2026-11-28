#!/usr/bin/env bash
set -euo pipefail

if command -v node >/dev/null 2>&1; then
    echo '========'
    echo "installed"
    node -v
    exit 0
fi

# >> install
sudo apt-get update -y
# <<

# >> https://nodesource.com/products/distributions
sudo apt-get install -y curl
curl -fsSL https://deb.nodesource.com/setup_26.x | sudo -E bash -
sudo apt-get install -y nodejs
node -v
# <<

# >> install plugin
exit

npm install --global corepack
corepack enable pnpm

npm install -g \
    npm-check-updates \
    pm2 \
    nodemon

pm2 install pm2-logrotate
# <<
