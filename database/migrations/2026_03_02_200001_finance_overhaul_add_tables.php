<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Add description to finance_funds
        Schema::table('finance_funds', function (Blueprint $table) {
            $table->text('description')->nullable()->after('owner_type');
        });

        // Member Contributions (tithe/offering breakdown by group)
        Schema::create('member_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('finance_transactions')->cascadeOnDelete();
            $table->string('member_group'); // Ban Chấp sự, Trung lão, Thanh tráng, Thanh niên, Thiếu nhi
            $table->unsignedInteger('people_count')->default(0);
            $table->bigInteger('amount')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Fund Transfers (move money between funds)
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_fund_id')->constrained('finance_funds')->cascadeOnDelete();
            $table->foreignId('to_fund_id')->constrained('finance_funds')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->text('note')->nullable();
            $table->date('transfer_date');
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add report_finance permission
        $reportPerm = Permission::firstOrCreate(['name' => 'report_finance', 'guard_name' => 'web']);
        $roles = ['Super_Admin', 'Pastor'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($reportPerm);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('member_contributions');

        Schema::table('finance_funds', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Permission::where('name', 'report_finance')->delete();
    }
};
