#!/usr/bin/env bash
set -euo pipefail

PWD_FILE=/miz_data/.pwd

OLD_PWD=""
if [ -e "$PWD_FILE" ]; then
    chattr -i "$PWD_FILE" 2>/dev/null || true
    OLD_PWD="$(cat "$PWD_FILE")"
fi

while true; do
    if [ -n "$OLD_PWD" ]; then
        read -r -s -p "Nhap mat khau quan tri (Enter de giu nguyen password cu): " pwd_input
    else
        read -r -s -p "Nhap mat khau quan tri (toi thieu 6 ky tu): " pwd_input
    fi
    echo

    if [ -z "$pwd_input" ] && [ -n "$OLD_PWD" ]; then
        new_pwd="$OLD_PWD"
        break
    elif [ "${#pwd_input}" -ge 6 ]; then
        new_pwd="$pwd_input"
        break
    else
        echo "Mat khau phai co it nhat 6 ky tu. Vui long nhap lai."
    fi
done

echo "$new_pwd" > "$PWD_FILE"
chmod 600 "$PWD_FILE"
chown root:root "$PWD_FILE"

if ! chattr +i "$PWD_FILE" 2>/dev/null; then
    echo "[!] Canh bao: khong the dat immutable cho $PWD_FILE" >&2
fi
