<?php
// grant_thanhtrang.php — Grant all features for tb.thanhtrang
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$u = \App\Models\User::where('email','tb.thanhtrang@httlthanhmyloi.com')->first();
if (!$u) { echo "NOT FOUND\n"; exit; }

$service = new \App\Services\PortalService();
$count = $service->grantSuperadminFullAccess($u);
echo "GRANTED {$count} permissions to {$u->name}\n";
