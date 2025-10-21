#!/usr/bin/env bash

echo 'fix cerbot renew'

LIVE_BASE="/etc/letsencrypt/live"
RENEW_BASE="/etc/letsencrypt/renewal"
mkdir -p "$RENEW_BASE"

shopt -s nullglob
for d in "$LIVE_BASE"/*/; do
  name="$(basename "$d")"

  echo $name;

  [[ -d "$d" ]] || continue
  out="$RENEW_BASE/$name.conf"

  cat >"$out" <<EOF
# renew_before_expiry = 30 days
version = 4.2.0
archive_dir = /etc/letsencrypt/archive/${name}
cert = /etc/letsencrypt/live/${name}/cert.pem
privkey = /etc/letsencrypt/live/${name}/privkey.pem
chain = /etc/letsencrypt/live/${name}/chain.pem
fullchain = /etc/letsencrypt/live/${name}/fullchain.pem

# Options used in the renewal process
[renewalparams]
account = cf8c93022f2460dda6382e5c95ec6dd3
authenticator = dns-cloudflare
dns_cloudflare_credentials = /www/data/certbot_dns_cloudflare.ini
server = https://acme-v02.api.letsencrypt.org/directory
key_type = ecdsa

[acme_renewal_info]
ari_retry_after = 2025-10-07T12:26:09
EOF
done
