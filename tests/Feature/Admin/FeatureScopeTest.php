<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Feature;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class FeatureScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\InitialSeeder::class);
    }

    #[Test]
    public function superadmin_can_assign_feature_to_department()
    {
        $admin = User::factory()->create(['is_superadmin' => true]);
        $dept = Department::create(['code' => 'D1', 'name' => 'Dept 1']);
        $feature = Feature::create(['name' => 'Test', 'slug' => 'test_feature']);

        $response = $this->actingAs($admin)
            ->postJson("/admin/features/assign", [
                'department_ids' => [$dept->id],
                'feature_id' => $feature->id,
                'scope' => 'specific',
                'data_scope' => 'dept',
                'is_active' => true
            ]);

        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('feature_department', [
            'department_id' => $dept->id,
            'feature_id' => $feature->id,
            'data_scope' => 'dept'
        ]);
    }

    #[Test]
    public function regular_user_cannot_assign_features()
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $dept = Department::create(['code' => 'D2', 'name' => 'Dept 2']);
        $feature = Feature::create(['name' => 'Test 2', 'slug' => 'test_feature_2']);

        // Acting as a regular authenticated user who lacks superadmin privileges
        $response = $this->actingAs($user)
            ->postJson("/admin/features/assign", [
                'department_ids' => [$dept->id],
                'feature_id' => $feature->id,
                'scope' => 'global', // Intentional invalid mismatch to test basic authorization first
                'data_scope' => 'global',
                'is_active' => true
            ]);

        // Expecting a 403 Forbidden
        $response->assertForbidden();
    }
}
