<?php

class miz_apache
{
    public static function reload()
    {
        passthru('apachectl configtest', $cmd_status);

        if ($cmd_status !== 0) {
            exit(1);
        }

        passthru('systemctl reload apache2');
    }
}
