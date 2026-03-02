<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach($users as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Roles: {$roles}\n";
}
