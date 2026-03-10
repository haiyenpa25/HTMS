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
        Schema::create('email_broadcasts', function (Blueprint $table) {
            $table->id();
            
            $table->string('subject');
            $table->longText('content'); 
            
            // Lọc đối tượng nhận
            $table->json('target_roles')->nullable(); // Gửi cho Role nào (null = all)
            $table->json('target_departments')->nullable(); // Gửi cho Ban nào
            
            // Trạng thái (draft, sending, completed, failed)
            $table->enum('status', ['draft', 'sending', 'completed', 'failed'])->default('draft');
            
            $table->integer('total_recipients')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_broadcasts');
    }
};
