# Autocomplete cho lệnh `miz`
# - Khi chưa có subcommand: gợi ý theo các file .sh trong /www/miz/src (không có đuôi .sh)
# - Khi đã có subcommand: tắt toàn bộ autocomplete

# Gợi ý file .sh khi chưa có subcommand
complete -c miz -f -n 'test (count (commandline -opc)) -eq 1' -a "(for f in /www/miz/src/*.sh
    if test -f \$f
        basename -s .sh \$f
    end
end)"

# Tắt autocomplete khi đã có subcommand
complete -c miz -n 'test (count (commandline -opc)) -gt 1' -f