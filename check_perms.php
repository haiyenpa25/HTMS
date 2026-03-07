<?php
// Run: php artisan tinker < check_perms.php

$email = 'tb.thanhtrang@httlthanhmyloi.com';
$u = App\Models\User::where('email', $email)->first();

if (!$u) {
    echo "USER NOT FOUND: $email\n";
    exit;
}

echo "=== User: {$u->name} (id={$u->id}) ===\n";
echo "Roles: " . $u->getRoleNames()->implode(', ') . "\n";

$all = App\Models\UserDepartmentFeature::where('user_id', $u->id)
    ->with(['feature', 'department'])
    ->get();

echo "\nAll user_department_features rows: " . $all->count() . "\n";

$enabled = $all->where('is_enabled', true);
echo "Enabled rows: " . $enabled->count() . "\n";

foreach ($enabled as $r) {
    echo "  dept={$r->department?->name} (block={$r->department?->block}) | feat={$r->feature?->slug} | enabled={$r->is_enabled}\n";
}

if ($all->count() > 0 && $enabled->count() === 0) {
    echo "\nWARNING: Records exist but ALL are is_enabled=0!\n";
}
if ($all->count() === 0) {
    echo "\nWARNING: No records AT ALL in user_department_features for this user!\n";
    echo "Admin never saved permissions - just checking toggles without saving?\n";
}
