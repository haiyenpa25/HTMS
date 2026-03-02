<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach($users as $u) {
    $hasPortal = $u->can('access_department_portal') ? 'Yes' : 'No';
    $hasSpeaker = $u->can('manage_speakers') ? 'Yes' : 'No';
    echo "Email: {$u->email}, Portal: {$hasPortal}, Speaker: {$hasSpeaker}\n";
}
