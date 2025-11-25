<?php
// Prevents re-execution if this file is included twice
if (defined('SERVER')) { return; } 
// PHP 7 compatibility for str_contains
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && strpos($haystack ?? '', $needle) !== false;
    }
}

$hostHeader = $_SERVER['HTTP_HOST'] ?? '';
$addr       = $_SERVER['SERVER_ADDR'] ?? '';
$onLocal    = str_contains($hostHeader, 'localhost') || str_contains($addr, '127.0.0.1');

// MAMP defaults
$MAMP_HOST   = '127.0.0.1';
$MAMP_PORT   = 8889;
$MAMP_USER   = 'root';
$MAMP_PASS   = 'root';
$MAMP_DB     = 'dbforlab';
// MAMP socket path (helps if PHP prefers sockets)
$MAMP_SOCKET = '/Applications/MAMP/tmp/mysql/mysql.sock';

if ($onLocal) {
    define('SERVER',   $MAMP_HOST);
    define('PORT',     $MAMP_PORT);
    define('USERNAME', $MAMP_USER);
    define('PASSWD',   $MAMP_PASS);
    define('DATABASE', $MAMP_DB);
    define('SOCKET',   $MAMP_SOCKET);
} else {
    // School / production
    define('SERVER',   '127.0.0.1');   // <- change to your real DB host/IP
    define('PORT',     3306);
    define('USERNAME', 'akua.oduro');
    define('PASSWD',   'Kiki@2025Pass!');
    define('DATABASE', 'ecommerce_2025A_akua_oduro');
    define('SOCKET',   null);
}
