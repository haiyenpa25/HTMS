<?php
// reset_pwd.php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u = User::where('email', 'superadmin@httlthanhmyloi.com')->first();
if ($u) {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super_Admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Pastor', 'guard_name' => 'web']);

    $u->password = Hash::make('Abc.1234');
    $u->syncRoles(['Super_Admin', 'Pastor']);
    $u->save();
    echo "Reset success!\n";
} else {
    echo "User not found!\n";
}
