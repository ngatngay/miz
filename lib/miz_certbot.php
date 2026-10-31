<?php

class miz_certbot {
    public static function has($key) {
        return file_exists('/etc/letsencrypt/live/' . $key . '/fullchain.pem');
    }
}
