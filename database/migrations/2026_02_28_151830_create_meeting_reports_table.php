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
        Schema::create('meeting_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            // Church
            $table->integer('total_attendance')->nullable();
            $table->json('department_attendances')->nullable();
            // Department
            $table->integer('total_members_present')->nullable();
            $table->integer('bible_quiz_correct_count')->nullable();
            $table->integer('memory_verse_memorized_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_reports');
    }
};
