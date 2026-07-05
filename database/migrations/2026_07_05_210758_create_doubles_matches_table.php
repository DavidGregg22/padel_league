<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doubles_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pair1_id')->constrained('double_pairs')->cascadeOnDelete();
            $table->foreignId('pair2_id')->constrained('double_pairs')->cascadeOnDelete();
            $table->unsignedTinyInteger('score1')->nullable(); // sets won by pair1
            $table->unsignedTinyInteger('score2')->nullable(); // sets won by pair2
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doubles_matches');
    }
};
