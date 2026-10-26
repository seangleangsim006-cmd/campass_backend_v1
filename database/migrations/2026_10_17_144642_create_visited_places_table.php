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
        Schema::create('visited_places', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('place_id')->constrained()->cascadeOnDelete();
        $table->timestamp('visited_at');

        $table->primary(['user_id', 'place_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visited_places');
    }
};
