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
        Schema::create('place_stories', function (Blueprint $table) {
            $table->id();
        $table->foreignId('place_id')->constrained()->cascadeOnDelete();
        $table->json('title');
        $table->json('transcript')->nullable();
        $table->unsignedInteger('duration_sec')->nullable();
        $table->boolean('autoplay_on_arrival')->default(true);
        $table->integer('sort_order')->default(0);
        $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
        $table->timestamps();
        $table->softDeletes();

        $table->index(['status', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_stories');
    }
};
