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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type', 50)->nullable(); // pdf, docx, xlsx...
            $table->integer('file_size')->nullable(); // in bytes
            
            $table->enum('category', [
                'general', 'policy', 'meeting_minute', 'manual', 'form', 'other'
            ])->default('general');
            
            $table->enum('visibility', [
                'public', // All members can view
                'internal', // Only active members
                'leadership', // Pastor, Super Admin, and specific Role
                'private' // Only uploader and Super Admin
            ])->default('public');

            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            
            // Tùy chọn: Gắn tài liệu này vào một Ban ngành cụ thể (nếu là Biên bản của ban đó)
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
