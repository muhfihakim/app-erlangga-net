<?php

return [
    'host' => env('OLT_HOST', '10.10.10.2'),
    'port' => env('OLT_PORT', 23),
    'username' => env('OLT_USERNAME', 'admin'),
    'password' => env('OLT_PASSWORD', 'password'),

    // SNMP Monitoring Configuration (RadbooX ZTE Profile)
    'driver' => env('OLT_DRIVER', 'snmp'), // 'snmp' (recommended, fast) or 'telnet'
    'snmp_ro' => env('OLT_SNMP_RO', 'radbooxro'),
    'snmp_rw' => env('OLT_SNMP_RW', 'radbooxrw'),
    'snmp_port' => env('OLT_SNMP_PORT', 161),
    'snmp_timeout' => env('OLT_SNMP_TIMEOUT', 2),
];
