<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_user', function (Blueprint $table) {
            // singles, doubles, both
            $table->enum('plays', ['singles', 'doubles', 'both'])->default('both')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('club_user', function (Blueprint $table) {
            $table->dropColumn('plays');
        });
    }
};
