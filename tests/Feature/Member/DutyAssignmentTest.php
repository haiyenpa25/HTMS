<?php

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\DepartmentRole;
use App\Models\DutyAssignment;
use PHPUnit\Framework\Attributes\Test;

class DutyAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\InitialSeeder::class);
    }

    #[Test]
    public function member_can_accept_their_own_duty_assignment()
    {
        // 1. Create a User + Member
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        // 2. Create an Assignment for this member
        $dept = Department::create(['code' => 'D1', 'name' => 'Dept 1']);
        $meeting = Meeting::create(['department_id' => $dept->id, 'name' => 'Test', 'date' => now()->toDateString(), 'time' => '09:00:00']);
        $role = DepartmentRole::create(['department_id' => $dept->id, 'name' => 'Role']);

        $assignment = DutyAssignment::create([
            'meeting_id' => $meeting->id,
            'department_role_id' => $role->id,
            'slot' => 1,
            'member_id' => $member->id,
            'status' => 'pending'
        ]);

        // 3. Act: Accept the duty
        $response = $this->actingAs($user)
            ->patch("/member/duty/{$assignment->id}/status", ['status' => 'accepted']);

        // 4. Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Đã cập nhật trạng thái phân công.']);
        
        $this->assertDatabaseHas('duty_assignments', [
            'id' => $assignment->id,
            'status' => 'accepted'
        ]);
    }

    #[Test]
    public function member_cannot_accept_another_members_duty_assignment()
    {
        // 1. Create User A (The Attacker)
        $userA = User::factory()->create();
        $memberA = Member::factory()->create(['user_id' => $userA->id]);

        // 2. Create User B (The Victim)
        $userB = User::factory()->create();
        $memberB = Member::factory()->create(['user_id' => $userB->id]);

        // 3. Create an Assignment for Victim (User B)
        $dept = Department::create(['code' => 'D2', 'name' => 'Dept 2']);
        $meeting = Meeting::create(['department_id' => $dept->id, 'name' => 'Test 2', 'date' => now()->toDateString(), 'time' => '09:00:00']);
        $role = DepartmentRole::create(['department_id' => $dept->id, 'name' => 'Role 2']);

        $assignment = DutyAssignment::create([
            'meeting_id' => $meeting->id,
            'department_role_id' => $role->id,
            'slot' => 1,
            'member_id' => $memberB->id,
            'status' => 'pending'
        ]);

        // 4. Act: User A attempts to accept User B's duty
        $response = $this->actingAs($userA)
            ->patch("/member/duty/{$assignment->id}/status", ['status' => 'accepted']);

        // 5. Assert: Must be blocked (Forbidden or Not Found)
        $response->assertStatus(403);
    }
}
