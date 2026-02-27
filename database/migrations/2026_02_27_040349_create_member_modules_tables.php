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
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('related_member_id')->constrained('members')->cascadeOnDelete();
            $table->string('type'); // parent, spouse, child, guardian
            $table->timestamps();
        });

        Schema::create('care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('caregiver_id')->constrained('members')->cascadeOnDelete();
            $table->date('visit_date');
            $table->text('summary');
            $table->text('notes')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('member_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('status'); // enrolled, completed, dropped
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });

        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('member_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('talent_id')->constrained('talents')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_talents');
        Schema::dropIfExists('talents');
        Schema::dropIfExists('member_courses');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('care_logs');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('households');
    }
};
