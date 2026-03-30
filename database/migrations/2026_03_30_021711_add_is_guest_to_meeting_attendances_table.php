<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_attendances', function (Blueprint $table) {
            // true = khách vãng lai (người chưa chính thức), không cần member_id thực
            $table->boolean('is_guest')->default(false)->after('quiz_score');
            $table->string('guest_name')->nullable()->after('is_guest');
            $table->string('guest_phone')->nullable()->after('guest_name');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_attendances', function (Blueprint $table) {
            $table->dropColumn(['is_guest', 'guest_name', 'guest_phone']);
        });
    }
};
