<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\FeatureDepartment;
use App\Models\Church;

class SystemSkeletonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder sets up the "Skeleton" (Khung sườn) required for the system to operate
     * on a fresh installation.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting System Skeleton Seeding...');

        // 1. Initial Fundamental Data (Permissions, Roles, Church)
        $this->call(PermissionSeeder::class);
        
        // Note: Church creation logic from InitialSeeder
        Church::firstOrCreate(
            ['email' => env('CHURCH_EMAIL', 'contact@' . env('SYSTEM_DOMAIN', 'httlthanhmyloi.com'))],
            [
                'name' => env('CHURCH_NAME', 'Hội Thánh Tin Lành'),
                'address' => env('CHURCH_ADDRESS', 'Địa chỉ Hội Thánh'),
                'phone_number' => env('CHURCH_PHONE', '0123456789'),
            ]
        );
        $this->command->info('✅ Church info initialized.');

        // 2. Organization Structure (Permissions, Roles, Departments)
        $this->call(OrgStructureSeeder::class);
        $this->command->info('✅ Org structure and permissions initialized.');

        // 3. Register Product Features (Attendance, Finance, etc.)
        $this->call(FeatureSeeder::class);
        $this->command->info('✅ Product features registered.');

        // 4. Configure Matrix Access Control (Tier 1: Block-level visibility)
        $this->seedFeatureAssignments();
        $this->command->info('✅ Tier 1 Feature Visibility (Blocks) configured.');

        $this->command->info('✨ System Skeleton completed successfully!');
    }

    /**
     * Standardize which features are visible for each block type.
     */
    private function seedFeatureAssignments(): void
    {
        $features = Feature::all()->keyBy('slug');

        $configs = [
            // --- ACTIVITIES BLOCK (Ban Ngành Sinh Hoạt) ---
            'activities' => [
                'attendance', 'visitation', 'members', 'reports', 'assignments', 'finance'
            ],

            // --- MINISTRY BLOCK (Ban Mục Vụ / GD) ---
            'ministry' => [
                'education-classes', 'education-attendance', 'education-offering', 'education-report'
            ],

            // --- LEADERSHIP BLOCK (Ban Chấp Sự / Lãnh đạo) ---
            'leadership' => [
                'attendance', 'reports', 'finance'
            ]
        ];

        foreach ($configs as $blockType => $slugs) {
            foreach ($slugs as $slug) {
                if (isset($features[$slug])) {
                    FeatureDepartment::updateOrCreate([
                        'feature_id' => $features[$slug]->id,
                        'block_type' => $blockType,
                        'department_id' => null, // Apply to entire block
                    ]);
                }
            }
        }
    }
}
