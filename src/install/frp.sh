#!/bin/bash
set -e

# Phiên bản FRP muốn cài đặt
FRP_VERSION="0.64.0"
FRP_DIR="/opt/frp"

# Tải FRP bằng curl
cd /tmp
curl -L -o frp_${FRP_VERSION}_linux_amd64.tar.gz \
  https://github.com/fatedier/frp/releases/download/v${FRP_VERSION}/frp_${FRP_VERSION}_linux_amd64.tar.gz

# Giải nén
tar -xzf frp_${FRP_VERSION}_linux_amd64.tar.gz

# Tạo thư mục đích và copy
sudo mkdir -p ${FRP_DIR}
sudo cp -r frp_${FRP_VERSION}_linux_amd64/* ${FRP_DIR}/

# Xóa file tạm
rm -rf frp_${FRP_VERSION}_linux_amd64*
echo "FRP ${FRP_VERSION} đã được cài đặt vào ${FRP_DIR}"
