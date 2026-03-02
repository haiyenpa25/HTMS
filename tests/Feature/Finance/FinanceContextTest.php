<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\FinanceFund;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FinanceContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles & Permissions
        $this->seed(\Database\Seeders\InitialSeeder::class);
    }

    public function test_super_admin_can_switch_to_any_department_context()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Pastor');

        $dept = Department::create(['code' => 'TN', 'name' => 'Thanh Niên']);

        $response = $this->actingAs($admin)
            ->post(route('finance.switch-context'), [
                'department_id' => $dept->id,
            ]);

        $response->assertSessionHas('active_finance_dept_id', $dept->id);
    }

    public function test_department_lead_can_only_switch_to_their_own_department()
    {
        $lead = User::factory()->create();
        $lead->assignRole('Department_Lead');
        $lead->givePermissionTo('view_finance');

        $ownDept = Department::create(['code' => 'TN1', 'name' => 'Thiếu Nhi']);
        $otherDept = Department::create(['code' => 'PN1', 'name' => 'Phụ Nữ']);

        $ownDept->members()->attach($lead->id, ['role' => 'Truong_Ban']);

        // Can switch to own
        $response1 = $this->actingAs($lead)
            ->post(route('finance.switch-context'), [
                'department_id' => $ownDept->id,
            ]);
        $response1->assertSessionHas('active_finance_dept_id', $ownDept->id);

        // Cannot switch to other
        $response2 = $this->actingAs($lead)
            ->post(route('finance.switch-context'), [
                'department_id' => $otherDept->id,
            ]);
        $response2->assertStatus(403);
    }

    public function test_finance_middleware_blocks_unauthorized_users()
    {
        $user = User::factory()->create();
        // No roles, no permissions
        
        $response = $this->actingAs($user)->get(route('finance.index'));
        
        $response->assertStatus(403);
    }
}
