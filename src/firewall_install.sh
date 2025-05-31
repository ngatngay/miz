echo "===> Đang kiểm tra các dịch vụ tường lửa xung đột..."

FW_SERVICES=("iptables" "ufw" "firewalld")

for fw in "${FW_SERVICES[@]}"; do
    sudo systemctl stop "$fw" 2>/dev/null || true
    sudo systemctl disable "$fw" 2>/dev/null || true
done

echo "===> Gỡ các gói: ${FIREWALL_SERVICES[*]}"
sudo apt remove --purge -y "${FW_SERVICES[@]}"
sudo apt autoremove -y

echo "===> Đã hoàn tất."

echo 'install nftables'

apt update 1>/dev/null
apt install nftables 1>/dev/null

sudo systemctl enable nftables
sudo systemctl start nftables

cptpl nftables.conf /etc/nftables.conf
nft -f /etc/nftables.conf

echo 'done'