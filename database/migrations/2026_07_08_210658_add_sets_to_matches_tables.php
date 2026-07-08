<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('singles_matches', function (Blueprint $table) {
            // Store each set as JSON: [{"p1":6,"p2":4},{"p1":3,"p2":6}]
            $table->json('sets')->nullable()->after('score2');
        });

        Schema::table('doubles_matches', function (Blueprint $table) {
            $table->json('sets')->nullable()->after('score2');
        });
    }

    public function down(): void
    {
        Schema::table('singles_matches', function (Blueprint $table) {
            $table->dropColumn('sets');
        });
        Schema::table('doubles_matches', function (Blueprint $table) {
            $table->dropColumn('sets');
        });
    }
};
