<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingFinance;
use App\Models\OrgMembership;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingFinanceScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create necessary roles
        Role::firstOrCreate(['name' => 'Pastor']);
        Role::firstOrCreate(['name' => 'Member']);
    }

    public function test_member_can_only_view_own_department_meetings(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('Member');
        $member1 = \App\Models\Member::create(['full_name' => 'Member 1', 'internal_id' => 'M1', 'user_id' => $user1->id]);
        
        $user2 = User::factory()->create();
        $user2->assignRole('Member');

        $dept1 = Department::create(['name' => 'Dept 1', 'block' => 'activities', 'code' => 'D1']);
        $dept2 = Department::create(['name' => 'Dept 2', 'block' => 'activities', 'code' => 'D2']);

        $orgRole = \App\Models\OrgRole::create(['name' => 'Thành viên', 'code' => 'MEMBER']);

        // Assign user1 to dept1
        OrgMembership::create([
            'member_id' => $member1->id,
            'model_type' => Department::class,
            'model_id' => $dept1->id,
            'org_role_id' => $orgRole->id
        ]);

        $meeting1 = Meeting::create([
            'type' => 'department',
            'department_id' => $dept1->id,
            'date' => '2026-03-01',
            'time' => '09:00:00',
            'topic' => 'Dept 1 Meeting'
        ]);

        $meeting2 = Meeting::create([
            'type' => 'department',
            'department_id' => $dept2->id,
            'date' => '2026-03-01',
            'time' => '10:00:00',
            'topic' => 'Dept 2 Meeting'
        ]);

        // User 1 should only see Meeting 1
        $accessibleByUser1 = Meeting::accessibleBy($user1)->pluck('id')->toArray();
        $this->assertContains($meeting1->id, $accessibleByUser1);
        $this->assertNotContains($meeting2->id, $accessibleByUser1);
    }

    public function test_unapproved_finances_excluded_from_total_sum(): void
    {
        $meeting = Meeting::create([
            'type' => 'church',
            'date' => '2026-03-01',
            'time' => '09:00:00',
            'topic' => 'Sunday Service'
        ]);

        MeetingFinance::create([
            'meeting_id' => $meeting->id,
            'amount' => 500000,
            'type' => 'thu',
            'category' => 'Dâng hiến',
            'status' => 'approved' // Approved
        ]);

        MeetingFinance::create([
            'meeting_id' => $meeting->id,
            'amount' => 200000,
            'type' => 'thu',
            'category' => 'Dâng hiến',
            'status' => 'pending' // Pending
        ]);

        $totalApproved = MeetingFinance::approved()->where('meeting_id', $meeting->id)->sum('amount');
        
        // Assert that only the 500000 is summed up
        $this->assertEquals(500000, $totalApproved);
    }
}
