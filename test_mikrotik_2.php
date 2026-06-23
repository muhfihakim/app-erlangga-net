<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\MikroTikService::class);
$pppoe = $service->getPppoeActive();

echo "Success: " . ($pppoe['success'] ? 'true' : 'false') . "\n";
if (isset($pppoe['error'])) {
    echo "Error: " . $pppoe['error'] . "\n";
}
echo "Count data: " . (isset($pppoe['data']) ? count($pppoe['data']) : 0) . "\n";
