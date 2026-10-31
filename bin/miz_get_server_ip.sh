#!/usr/bin/env bash

ip_addr="$(ip route get to 8.8.8.8 2> /dev/null | grep -m 1 -o 'src [0-9.]*' | cut -d ' ' -f 2)"

if [ -z "$ip_addr" ]; then
    ip_addr=$(_trim "$(curl "https://checkip.amazonaws.com")")
fi

echo "$ip_addr"

