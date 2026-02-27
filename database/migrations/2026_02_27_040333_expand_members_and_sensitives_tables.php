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
        Schema::table('members', function (Blueprint $table) {
            $table->string('address')->nullable()->after('phone');
            $table->string('visit_location')->nullable()->after('address');
            $table->string('member_type', 50)->nullable()->after('visit_location');
            $table->date('faith_date')->nullable()->after('member_type');
            $table->boolean('is_baptized')->default(false)->after('faith_date');
            $table->date('baptism_date')->nullable()->after('is_baptized');
            $table->date('joined_date')->nullable()->after('baptism_date');
            $table->string('attendance_status', 50)->nullable()->after('joined_date');
            $table->text('general_notes')->nullable()->after('attendance_status');
            $table->unsignedBigInteger('household_id')->nullable()->after('user_id');
        });

        Schema::table('member_sensitives', function (Blueprint $table) {
            $table->text('prayer_concerns')->nullable()->after('background_notes');
            $table->text('pastoral_notes')->nullable()->after('prayer_concerns');
            $table->string('occupation')->nullable()->after('pastoral_notes');
            $table->string('marital_status')->nullable()->after('occupation');
        });
    }

    public function down(): void
    {
        Schema::table('member_sensitives', function (Blueprint $table) {
            $table->dropColumn(['prayer_concerns', 'pastoral_notes', 'occupation', 'marital_status']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'visit_location', 'member_type', 'faith_date', 
                'is_baptized', 'baptism_date', 'joined_date', 
                'attendance_status', 'general_notes', 'household_id'
            ]);
        });
    }
};
