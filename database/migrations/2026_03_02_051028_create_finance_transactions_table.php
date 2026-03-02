<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained('finance_funds')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->enum('type', ['income', 'expense']);
            $table->string('category')->nullable();
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->foreignId('session_metrics_id')->nullable()->constrained('finance_session_metrics')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
