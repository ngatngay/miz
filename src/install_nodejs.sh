#nodejs

if cmd_exists node; then
    efw =
    echo 'installed'
    node -v
    exit
fi

curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
node -v

npm install --global corepack
corepack enable pnpm

npm install -g \
    npm-check-updates \
    pm2 \
    nodemon

pm2 install pm2-logrotate
