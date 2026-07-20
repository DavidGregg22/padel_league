<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('available_date');
            $table->time('end_time')->nullable()->after('start_time');
        });

        // Drop the old unique constraint and add new one including times
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropUnique(['club_id', 'user_id', 'available_date']);
            $table->unique(['club_id', 'user_id', 'available_date', 'start_time'], 'avail_unique');
        });
    }

    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropUnique('avail_unique');
            $table->unique(['club_id', 'user_id', 'available_date']);
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
