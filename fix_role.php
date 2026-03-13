<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

try {
    $role = Role::firstOrCreate(['name' => 'Super_Admin', 'guard_name' => 'web']);
    $user = User::where('email', 'superadmin@httlthanhmyloi.com')->first();
    if ($user) {
        $user->assignRole($role);
        echo "Thanh cong gan quyen Super_Admin cho: " . $user->email . "\n";
    } else {
        echo "Khong tim thay user\n";
    }
} catch (\Exception $e) {
    echo "Loi: " . $e->getMessage() . "\n";
}
