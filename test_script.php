<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(App\Services\FeatureAssignmentService::class);

function checkUser($email, $app, $service) {
    echo "\n=== User: $email ===\n";
    $user = App\Models\User::where('email', 'like', $email . '%')->first();
    if (!$user) { echo "NOT FOUND\n"; return; }
    
    echo "Roles: " . implode(',', $user->getRoleNames()->toArray()) . "\n";
    $member = App\Models\Member::where('user_id', $user->id)->first();
    
    // Check memberships
    $memberships = App\Models\OrgMembership::where('member_id', $member?->id ?? 0)
        ->where('model_type', App\Models\Department::class)->get();
    foreach($memberships as $m) {
        $dept = App\Models\Department::find($m->model_id);
        if ($dept) {
            echo "  OrgMembership: {$dept->name} (block={$dept->block})\n";
            
            // Check Level 1
            $lvl1 = $service->getAvailableFeaturesForDepartment($dept);
            $enabled = array_keys(array_filter($lvl1));
            echo "  Level1 features: " . (count($enabled) ? implode(',', $enabled) : 'NONE') . "\n";
        }
    }
    
    // Check MAC Level 2
    $udfs = App\Models\UserDepartmentFeature::with(['feature','department'])
        ->where('user_id', $user->id)->where('is_enabled', true)->get();
    echo "  Level2 MAC (" . $udfs->count() . " records):\n";
    foreach($udfs as $udf) {
        echo "    " . ($udf->department?->name ?? '?') . " → " . ($udf->feature?->slug ?? '?') . "\n";
    }
}

checkUser('tb.thanhtrang', $app, $service);
checkUser('tb.bcdgd', $app, $service);
checkUser('tk.chapsu', $app, $service);
