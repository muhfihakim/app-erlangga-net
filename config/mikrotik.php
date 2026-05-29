<?php

return [
    'host' => env('MIKROTIK_HOST', '192.168.10.1'),
    'port' => (int) env('MIKROTIK_PORT', 8728),
    'username' => env('MIKROTIK_USER', 'admin'),
    'password' => env('MIKROTIK_PASS', ''),
    'interface' => env('MIKROTIK_INTERFACE', 'ether1'),
    'timeout' => (int) env('MIKROTIK_TIMEOUT', 10),
    'ssl' => env('MIKROTIK_SSL', false),
    'verify_peer' => env('MIKROTIK_SSL_VERIFY', false),
];
