<?php

namespace Tests\Feature\Deacon;

use App\Models\Department;
use App\Models\DeaconMonthlyReport;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DeaconReportIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure permission exists for tests
        Permission::firstOrCreate(['name' => 'view_deacon']);
        
        // Ensure "Ban Chấp Sự" exists for context middleware (ID=1)
        Department::firstOrCreate(['id' => 1], ['name' => 'Ban Chấp Sự', 'code' => 'BCS']);
    }

    #[Test]
    public function test_deacon_leader_can_approve_report()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $user->givePermissionTo('view_deacon');

        $report = DeaconMonthlyReport::create([
            'report_month' => now()->month,
            'report_year' => now()->year,
            'status' => 'submitted',
            'submitted_by' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_deacon_role' => 'head'])
            ->post("/deacon/report/{$report->id}/status", [
                'action' => 'approve'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('deacon_monthly_reports', [
            'id' => $report->id,
            'status' => 'approved'
        ]);
    }

    #[Test]
    public function test_regular_deacon_cannot_approve_report()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_deacon');

        $report = DeaconMonthlyReport::create([
            'report_month' => now()->month,
            'report_year' => now()->year,
            'status' => 'submitted',
            'submitted_by' => $user->id
        ]);

        // Attempting to approve as 'secretary' (or any standard role)
        $response = $this->actingAs($user)
            ->withSession(['active_deacon_role' => 'secretary'])
            ->post("/deacon/report/{$report->id}/status", [
                'action' => 'approve'
            ]);

        // Must be blocked
        $response->assertForbidden();
        
        $this->assertDatabaseHas('deacon_monthly_reports', [
            'id' => $report->id,
            'status' => 'submitted'
        ]);
    }
}
