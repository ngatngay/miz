<?php

class miz_haproxy
{
    
    public static function check_config()
    {
        passthru("haproxy -c -f /etc/haproxy/haproxy.cfg", $cmd_status);

        if ($cmd_status !== 0) {
            exit(1);
        }
    }

    public static function reload()
    {
        passthru("haproxy -c -f /etc/haproxy/haproxy.cfg", $cmd_status);

        if ($cmd_status !== 0) {
            exit(1);
        }

        passthru("systemctl reload haproxy");
    }
}
