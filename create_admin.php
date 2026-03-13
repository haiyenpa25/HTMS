<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::firstOrCreate(
        ['email' => 'superadmin@httlthanhmyloi.com'],
        ['name' => 'Super Admin', 'password' => Hash::make('Abc.1234')]
    );
    try {
        $user->assignRole('Super_Admin');
    } catch (\Exception $e) {
        $user->assignRole('Pastor'); // Fallback role if Super_Admin doesn't exist
    }
    echo "Created user: " . $user->email . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
