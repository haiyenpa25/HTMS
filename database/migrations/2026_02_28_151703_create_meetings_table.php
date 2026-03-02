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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['church', 'department'])->default('church');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('time');
            $table->string('topic')->nullable();
            $table->string('memory_verse')->nullable();
            $table->string('scripture')->nullable();
            $table->string('preacher')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
