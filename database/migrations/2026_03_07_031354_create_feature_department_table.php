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
        Schema::create('feature_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            
            // "activities", "ministry", "leadership", etc.
            $table->string('block_type'); 
            
            // Null means ALL departments in the block. 
            // ID means specifically this department.
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete(); 
            
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['feature_id', 'block_type', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_department');
    }
};
