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
        Schema::create('visitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('visitation_type', ['church', 'department'])->default('department');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->date('visit_date');
            $table->enum('reason', ['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác']);
            $table->text('content')->nullable(); // Sensitive content
            $table->text('prayer_points')->nullable();
            $table->string('gifts')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('visitation_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitation_id')->constrained('visitations')->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained('members')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitation_visitors');
        Schema::dropIfExists('visitations');
    }
};
