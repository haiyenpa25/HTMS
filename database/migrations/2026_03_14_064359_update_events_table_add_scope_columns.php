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
        Schema::table('events', function (Blueprint $table) {
            $table->enum('scope_type', ['global', 'department', 'internal', 'leadership'])->default('global')->after('visibility');
            $table->unsignedBigInteger('scope_id')->nullable()->after('scope_type');
            
            $table->foreign('scope_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['scope_id']);
            $table->dropColumn(['scope_type', 'scope_id']);
        });
    }
};
