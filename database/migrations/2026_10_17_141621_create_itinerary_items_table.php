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
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
        $table->foreignId('itinerary_id')->constrained()->cascadeOnDelete();
        $table->foreignId('place_id')->constrained()->restrictOnDelete();
        $table->unsignedTinyInteger('day_number');
        $table->integer('sort_order')->default(0);
        $table->string('time_suggestion', 20)->nullable();
        $table->json('note')->nullable();
        $table->timestamps();

        $table->index(['itinerary_id', 'day_number', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
    }
};
