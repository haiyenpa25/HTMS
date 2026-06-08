<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deacon_term_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deacon_id');          // FK → members.id
            $table->unsignedBigInteger('department_id');       // FK → departments.id
            $table->smallInteger('term_from');                 // Năm bắt đầu nhiệm kỳ (VD: 2024)
            $table->smallInteger('term_to');                   // Năm kết thúc nhiệm kỳ (VD: 2026)
            $table->string('term_label', 100)->nullable();     // "Nhiệm kỳ 2024-2026"
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable(); // FK → users.id
            $table->timestamps();

            $table->foreign('deacon_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

            $table->index(['deacon_id', 'term_from', 'term_to'], 'idx_deacon_term');
            $table->index(['department_id', 'term_from', 'term_to'], 'idx_dept_term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deacon_term_assignments');
    }
};
