<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\FinanceFund;
use App\Models\FinanceTransaction;

class FinanceTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\InitialSeeder::class);
    }

    public function test_user_can_create_transaction_and_it_is_pending_by_default()
    {
        $user = User::factory()->create();
        $user->assignRole('Team_Lead');
        $user->givePermissionTo('create_finance');
        // Doesn't have approve_finance

        $dept = Department::create(['code' => 'T1', 'name' => 'Test Dept']);
        $dept->members()->attach($user->id, ['role' => 'Thanh_Vien']);
        
        $fund = FinanceFund::create([
            'name' => 'Department Fund',
            'owner_type' => 'department',
            'owner_id' => $dept->id
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_finance_dept_id' => $dept->id])
            ->post(route('finance.transactions.store'), [
                'fund_id' => $fund->id,
                'amount' => 100000,
                'type' => 'income',
                'category' => 'Dâng hiến',
                'transaction_date' => now()->toDateString(),
            ]);

        $response->assertSessionHas('message');
        $this->assertDatabaseHas('finance_transactions', [
            'amount' => 100000,
            'status' => 'pending'
        ]);
    }

    public function test_transaction_auto_approves_if_user_has_approve_permission()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Pastor');

        $fund = FinanceFund::create([
            'name' => 'Church Fund',
            'owner_type' => 'church',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('finance.transactions.store'), [
                'fund_id' => $fund->id,
                'amount' => 500000,
                'type' => 'expense',
                'category' => 'Mua sắm',
                'transaction_date' => now()->toDateString(),
            ]);

        $this->assertDatabaseHas('finance_transactions', [
            'amount' => 500000,
            'status' => 'approved'
        ]);
    }

    public function test_admin_can_approve_pending_transaction()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Pastor');

        $fund = FinanceFund::create(['name' => 'Fund', 'owner_type' => 'church']);
        $transaction = FinanceTransaction::create([
            'fund_id' => $fund->id,
            'amount' => 1000,
            'type' => 'income',
            'transaction_date' => now(),
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)
            ->post(route('finance.transactions.approve', $transaction->id), [
                'status' => 'approved'
            ]);

        $this->assertDatabaseHas('finance_transactions', [
            'id' => $transaction->id,
            'status' => 'approved'
        ]);
    }
}
