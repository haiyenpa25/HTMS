<?php
// fix_permissions.php — Fix toàn bộ phân quyền hệ thống

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Feature;
use App\Models\Department;
use App\Models\FeatureDepartment;
use App\Models\User;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Models\UserDepartmentFeature;

echo "=== FIX PERMISSIONS SCRIPT ===\n\n";

// ──────────────────────────────────────────────────────
// STEP 1: Seed block-level features
// Logic: dùng scope='block' để tất cả ban trong block tự động có tính năng
// Không cần config riêng từng ban → dễ quản lý
// ──────────────────────────────────────────────────────
echo "STEP 1: Setup block-level FeatureDepartment...\n";

$activities_features = ['attendance', 'visitation', 'members', 'assignments', 'reports', 'finance'];
$education_features = ['education-classes', 'education-attendance', 'education-offering', 'education-report'];

// 1a: Activities block — 6 features cho toàn bộ block
foreach ($activities_features as $slug) {
    $feature = Feature::where('slug', $slug)->first();
    if (!$feature) { echo "  ❌ Feature not found: $slug\n"; continue; }

    FeatureDepartment::updateOrCreate(
        ['feature_id' => $feature->id, 'block_type' => 'activities', 'department_id' => null],
        ['scope' => 'block', 'is_active' => true]
    );
    echo "  ✅ activities block → $slug\n";
}

// 1b: Ministry block — Ban CĐGD có education features riêng (scope=specific)
$cdgd = Department::find(8); // Ban Cơ Đốc Giáo Dục
foreach ($education_features as $slug) {
    $feature = Feature::where('slug', $slug)->first();
    if (!$feature || !$cdgd) { echo "  ❌ Missing: $slug / CDGD\n"; continue; }

    FeatureDepartment::updateOrCreate(
        ['feature_id' => $feature->id, 'department_id' => $cdgd->id],
        ['scope' => 'specific', 'block_type' => 'ministry', 'is_active' => true]
    );
    echo "  ✅ CĐGD specific → $slug\n";
}

// ──────────────────────────────────────────────────────
// STEP 2: Ensure all users in each department have their MAC Level 2 (UserDepartmentFeature)
// Seed tất cả user có OrgMembership trong ban activities → full activities features
// ──────────────────────────────────────────────────────
echo "\nSTEP 2: Seed UserDepartmentFeature cho users có OrgMembership trong activities...\n";

$activityDepts = Department::where('block', 'activities')->get();
$activityFeatures = Feature::whereIn('slug', $activities_features)->get();

foreach ($activityDepts as $dept) {
    // Tìm tất cả member trong ban này (qua OrgMembership)
    $memberships = OrgMembership::where('model_type', Department::class)
        ->where('model_id', $dept->id)
        ->whereNotNull('member_id')
        ->get();

    foreach ($memberships as $membership) {
        $member = Member::find($membership->member_id);
        if (!$member || !$member->user_id) continue;
        
        $user = User::find($member->user_id);
        if (!$user) continue;

        foreach ($activityFeatures as $feature) {
            UserDepartmentFeature::updateOrCreate(
                ['user_id' => $user->id, 'department_id' => $dept->id, 'feature_id' => $feature->id],
                ['is_enabled' => true]
            );
        }
        echo "  ✅ {$user->email} → {$dept->name}\n";
    }
}

// STEP 2b: Seed cho users có UserDepartmentFeature nhưng không có OrgMembership (tb.thanhtrang case)
// tb.thanhtrang có MAC but no OrgMembership → cần thêm OrgMembership
echo "\nSTEP 2b: Ensure tb.thanhtrang có OrgMembership trong Ban Thanh Tráng...\n";
$thanhtrang_user = User::where('email', 'tb.thanhtrang@httlthanhmyloi.com')->first();
if ($thanhtrang_user) {
    $member = Member::where('user_id', $thanhtrang_user->id)->first();
    $banThanhTrang = Department::find(5); // Ban Thanh Tráng
    if ($member && $banThanhTrang) {
        $tbRole = OrgRole::where('code', 'tb')->first();
        $existingMembership = OrgMembership::where('member_id', $member->id)
            ->where('model_type', Department::class)
            ->where('model_id', $banThanhTrang->id)
            ->first();
        if (!$existingMembership) {
            OrgMembership::create([
                'member_id' => $member->id,
                'model_type' => Department::class,
                'model_id' => $banThanhTrang->id,
                'org_role_id' => $tbRole?->id,
                'is_active' => true,
            ]);
            echo "  ✅ Đã tạo OrgMembership cho {$thanhtrang_user->email} vào {$banThanhTrang->name}\n";
        } else {
            echo "  ℹ️  OrgMembership đã tồn tại cho {$thanhtrang_user->email}\n";
        }
        // Ensure MAC Level 2
        foreach ($activityFeatures as $feature) {
            UserDepartmentFeature::updateOrCreate(
                ['user_id' => $thanhtrang_user->id, 'department_id' => $banThanhTrang->id, 'feature_id' => $feature->id],
                ['is_enabled' => true]
            );
        }
        echo "  ✅ MAC Level 2 OK cho {$thanhtrang_user->email}\n";
    }
}

// ──────────────────────────────────────────────────────
// STEP 3: Fix tk.chapsu — cần role Deacon hoặc OrgMembership ở Ban Chấp Sự (ID=1, block=leadership)
// ──────────────────────────────────────────────────────
echo "\nSTEP 3: Fix tk.chapsu → gán role Deacon...\n";
$chapsu_user = User::where('email', 'like', 'tk.chapsu%')->first();
if ($chapsu_user) {
    if (!$chapsu_user->hasRole('Deacon')) {
        $chapsu_user->assignRole('Deacon');
        echo "  ✅ Đã gán role Deacon cho {$chapsu_user->email}\n";
    } else {
        echo "  ℹ️  {$chapsu_user->email} đã có role Deacon\n";
    }
} else {
    echo "  ❌ Không tìm thấy user tk.chapsu\n";
}

// ──────────────────────────────────────────────────────
// STEP 4: Seed MAC Level 2 cho CĐGD users đã có OrgMembership
// ──────────────────────────────────────────────────────
echo "\nSTEP 4: Seed UserDepartmentFeature cho users trong Ban CĐGD (ministry)...\n";
$cdgdFeatures = Feature::whereIn('slug', $education_features)->get();
$cdgdMemberships = OrgMembership::where('model_type', Department::class)
    ->where('model_id', 8) // CĐGD
    ->whereNotNull('member_id')
    ->get();

foreach ($cdgdMemberships as $m) {
    $member = Member::find($m->member_id);
    if (!$member || !$member->user_id) continue;
    $user = User::find($member->user_id);
    if (!$user) continue;

    foreach ($cdgdFeatures as $feature) {
        UserDepartmentFeature::updateOrCreate(
            ['user_id' => $user->id, 'department_id' => 8, 'feature_id' => $feature->id],
            ['is_enabled' => true]
        );
    }
    echo "  ✅ {$user->email} → CĐGD education features\n";
}

echo "\n=== DONE: All permissions fixed ===\n";
echo "Summary:\n";
echo "  - FeatureDepartment (Level 1) seeded: activities block (6 features), CĐGD specific (4 features)\n";
echo "  - OrgMembership: tb.thanhtrang ensured in Ban Thanh Tráng\n";
echo "  - Role Deacon: tk.chapsu ensured\n";
echo "  - UserDepartmentFeature (Level 2): seeded for all dept members\n";
