<?php

namespace Tests\Feature\Leadership;

use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LeadershipMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the "Ban Chấp Sự" (Deacon/Leadership block)
        Department::firstOrCreate(
            ['id' => 1], 
            ['name' => 'Ban Chấp Sự', 'code' => 'BCS', 'block' => 'leadership']
        );
    }

    #[Test]
    public function superadmin_can_view_metrics_dashboard()
    {
        $user = User::factory()->create(['is_superadmin' => true]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
    }

    #[Test]
    public function regular_user_is_redirected_from_metrics_dashboard()
    {
        $user = User::factory()->create(['is_superadmin' => false]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('portal.index'));
    }

    #[Test]
    public function regular_user_cannot_switch_to_leadership_context()
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $member = Member::factory()->create(['user_id' => $user->id]);
        
        // 1. Give user legitimate access to a ministry department
        $ministryDept = Department::create(['name' => 'Ministry', 'code' => 'MIN', 'block' => 'ministry']);
        $orgRole = \App\Models\OrgRole::firstOrCreate(
            ['name' => 'Thành viên'], 
            ['code' => 'MEMBER', 'level' => 1]
        );
        
        \App\Models\OrgMembership::create([
            'member_id' => $member->id,
            'model_type' => Department::class,
            'model_id' => $ministryDept->id,
            'org_role_id' => $orgRole->id,
            'is_active' => true
        ]);

        // 2. Target a leadership department that the user is NOT a member of
        $leadershipDept = Department::find(1);

        $response = $this->actingAs($user)->post('/ministry/switch-context', [
            'department_id' => $leadershipDept->id
        ]);

        $response->assertForbidden();
    }
}
