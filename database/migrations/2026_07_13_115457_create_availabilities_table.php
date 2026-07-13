<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('available_date');
            $table->timestamps();

            $table->unique(['club_id', 'user_id', 'available_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
