#!/usr/bin/env php
<?php

$certs = glob('/etc/letsencrypt/live/*/fullchain.pem');

foreach ($certs as $cert) {
    $domain = basename(dirname($cert));
    $pem_path = '/var/lib/haproxy/cert/9-' . $domain . '.pem';
    $key  = '/etc/letsencrypt/live/' . $domain . '/privkey.pem';

    if (!file_exists($key)) {
        echo "[E] KEY $domain\n";
        continue;
    }

    if (file_put_contents($pem_path, file_get_contents($cert) . file_get_contents($key)) === false) {
        echo "[E] WRITE $domain\n";
    }
}

passthru('chown -R haproxy:haproxy /var/lib/haproxy/cert');
passthru('chmod 700 /var/lib/haproxy/cert');
passthru('chmod -R 600 /var/lib/haproxy/cert/*');
