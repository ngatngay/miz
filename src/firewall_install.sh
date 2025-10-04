echo 'CÁC thao tác với firewall RẤT NGUY HIỂM, có thể khiến VPS không thể truy cập trong thời gian ngắn!'
echo
echo '[Enter] để tiếp tục.'
read

sudo systemctl enable nftables
sudo systemctl start nftables

cptpl nftables.conf /etc/nftables.conf

nft -c -f /etc/nftables.conf && sudo systemctl restart nftables

echo 'Firewall đã thiết lập xong.'

echo
echo 'Ấn Enter để xác nhận RESTART Docker.'
echo '- Không restart docker sẽ không dùng được các cổng public'
echo 'Nhấn Ctrl+C để HỦY!'
read  # chờ người dùng nhấn Enter

sudo systemctl restart docker

echo 'Hoàn thành restart Docker!'
