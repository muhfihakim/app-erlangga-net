<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$active = \MikroTik::pppoe()->getActiveSessions();
echo "Type: " . gettype($active) . "\n";
echo "Count: " . count($active) . "\n";
if (count($active) > 0) {
    echo "First key: " . array_key_first($active) . "\n";
    if (isset($active[0])) {
        echo "Is array of arrays\n";
    } else {
        echo "Is associative array\n";
    }
}
