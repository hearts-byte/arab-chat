<?php
// base system prefix
define('BOOM_PREFIX', '');

// optional base domain
define('BOOM_DOMAIN', '');

// default redis configuration
define('REDIS_IP', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_TIMEOUT', 0.2);
define('REDIS_PASS', '');

// you can edit these lines to configure new setting for your chat
define('BOOM_DHOST', 'mysql-qnf9.railway.internal');
define('BOOM_DUSER', 'root');
define('BOOM_DPASS', 'xsNOTIIvKkShhYMAFWpCwMKkmMvkKqKH');
define('BOOM_DNAME', 'railway');

// base system main path do not modify
define('BOOM_PATH', dirname(__DIR__));

// do not modify those variables
define('BOOM_CRYPT', '');
define('BOOM_INSTALL', 1);
define('BOOM', 1);
?>
