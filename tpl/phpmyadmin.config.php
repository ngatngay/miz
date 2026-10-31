<?php

$cfg['blowfish_secret'] = 'mizphpmyadminmizmizphpmyadminmiz';

/**
 * Servers configuration
 */
$i = 0;

/**
 * First server
 */
$i++;
/* Authentication type */
$cfg['Servers'][$i]['auth_type'] = 'cookie';
/* Server parameters */
$cfg['Servers'][$i]['host'] = 'localhost';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;
$cfg['Servers'][$i]['hide_db'] = '^(information_schema)$';

/**
 * phpMyAdmin configuration storage settings.
 */

$cfg['VersionCheck'] = false;
$cfg['NavigationTreeEnableGrouping'] = false;
$cfg['PmaNoRelation_DisableWarning'] = true;

