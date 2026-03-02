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
        Schema::create('finance_funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('owner_id')->nullable()->comment('Department ID or Null if Church level');
            $table->string('owner_type')->default('department')->comment('church, department');
            $table->timestamps();
            
            // Note: Not setting a strict foreign key to departments dynamically to keep flexibility 
            // if later expanding owner_type beyond just departments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_funds');
    }
};
