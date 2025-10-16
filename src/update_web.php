<?php

define('ACCESS', true);

require __DIR__ . '/../miz.php';

// init
$certbot_key = '/www/data/certbot_dns_cloudflare.ini';
if (!is_file($certbot_key)) {
    exit("chưa có key zone cloudflare để get ssl\n");
}

// form
$update_domain = readline("Nhập domain cần update (chỉ 1 domain, có thể để trống): ");
$update_domain = trim($update_domain);

$ssl_input = strtolower(trim(readline("Có update SSL không? (y/n): ")));
$update_ssl = $ssl_input === 'y';

echo "Thông tin vừa nhập:\n";
echo " - Domain: " . ($update_domain ?: '[toàn bộ]') . "\n";
echo " - Update SSL: " . ($update_ssl ? 'Có' : 'Không') . "\n";

$domains = domains_load();
$domains_ids = array_keys($domains);

// update toàn bộ, clean file
if (empty($update_domain)) {
    // tạo lại toàn bộ config
    //passthru('rm -rf /www/data/domain');
    passthru('mkdir -p /www/data/domain');
    passthru("a2dissite '9-*' >/dev/null 2>&1");


    // xoá config cũ
    $current_confs = array_merge(
        glob('/etc/php/*/fpm/pool.d/9-*.conf'),
        glob('/etc/apache2/sites-available/9-*.conf')
    );

    foreach ($current_confs as $file) {
        unlink($file);
    }
}

// xử lý
foreach ($domains as $domain_id => $domain) {
    $domain['domains_arr'] = explode(' ', $domain['domains']); 
    $domain['domain'] = $domain['domains_arr'][0];
    $domain['apache_xsendfilr_dir'] = '/www/web/' . $domain['domain'];

    $domain_conf_path = "/www/data/domain/$domain_id";
    $apache_ssl_conf = "$domain_conf_path/apache_ssl.conf";

    // có update domain, mà không tồn tại ở id này, next
    if ($update_domain && !in_array($update_domain, $domain['domains_arr'])) {
        continue;
    }
    
    echo "\n  -- " . $domain['domains'] . "\n";
    
    passthru("mkdir -p $domain_conf_path");
    
    // tạo file conf
    
    // ssl
    if ($update_ssl) {
        $c_args = [];

        foreach ($domain['domains_arr'] as $c_domain) {
            $c_args[] = "-d $c_domain";
        }

        $cmd = "certbot certonly --agree-tos --quiet --non-interactive --expand " .
               "--dns-cloudflare --dns-cloudflare-credentials $certbot_key " .
               "--cert-name " . $domain['domain'] . " " . implode(' ', $c_args);   
        $ssl_failed = 0;

        passthru($cmd, $ssl_failed);
        
        if ($ssl_failed !== 0) {
            echo "- Không lấy được SSL\n";
        } else {
            $c_content = "SSLCertificateFile /etc/letsencrypt/live/" . $domain['domain'] . "/fullchain.pem\n" .
            "SSLCertificateKeyFile /etc/letsencrypt/live/" . $domain['domain'] . "/privkey.pem\n";
            file_put_contents($apache_ssl_conf, $c_content);
        }
    }
    
    // ssl default, ưu tiên file có sẵn, mặc định là self sign
    if (is_file($apache_ssl_conf)) {
        $c_content = file_get_contents($apache_ssl_conf);
    } else {
        $c_content = "SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem\n" .
        "SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key\n";
    }

     file_put_contents($apache_ssl_conf, $c_content);

    // apache
    $apache_tpl = gen_tpl('apache_vhost.conf', $domain);
    
    // apache ssl
     $apache_tpl = tpl_replace_block($apache_tpl, 'ssl', $c_content);

    // apache php
    if (empty($domain['php'])) {
        $apache_tpl = tpl_replace_block($apache_tpl, 'php', '');
    }

    file_put_contents($domain_conf_path . '/apache.conf', $apache_tpl);

    copy("$domain_conf_path/apache.conf", '/etc/apache2/sites-available/9-' . $domain['domain'] . '.conf');
    
    if ($domain['status'] === 'on') {
        passthru('a2ensite 9-' . $domain['domain'] . ' 1>/dev/null');
    } else {
        passthru('a2dissite 9-' . $domain['domain'] . ' 1>/dev/null');
    }

    exec('apachectl configtest >/dev/null 2>&1', $o, $e);
    if ($e !== 0) {
        exec('apachectl configtest');
        exit;
    }
    
    // php
    if (!empty($domain['php'])) {
        $php_conf = $domain_conf_path . '/php-fpm.conf';
       
        $php_tpl = gen_tpl('php-fpm.conf', $domain);
        file_put_contents($php_conf, $php_tpl);

        passthru('rm -f /etc/php/*/fpm/pool.d/9-' . $domain['domain'] . '.conf');
        passthru('cp -f ' . $php_conf . ' /etc/php/' . $domain['php'] . '/fpm/pool.d/9-' . $domain['domain'] . '.conf');
    }
}

// end

$versions = ['5.6', '7.4', '8.0', '8.1', '8.2', '8.3', '8.4'];
foreach ($versions as $v) {
    passthru("systemctl reload php{$v}-fpm"); 
}

passthru("systemctl reload apache2");

passthru('chown -R www-data:www-data /www/data');

echo "\n\n";
echo "--- \n";
echo "updated \n";
