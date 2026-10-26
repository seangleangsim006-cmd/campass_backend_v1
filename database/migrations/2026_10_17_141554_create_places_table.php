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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
        $table->foreignId('destination_id')->constrained()->restrictOnDelete();
        $table->foreignId('place_category_id')->constrained('place_categories')->restrictOnDelete();
        $table->json('name');
        $table->string('slug', 150)->unique();
        $table->json('description')->nullable();
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->unsignedInteger('radius_m')->default(100);
        $table->json('opening_hours')->nullable();
        $table->decimal('entrance_fee', 8, 2)->nullable();
        $table->json('fee_note')->nullable();
        $table->unsignedInteger('suggested_duration_min')->nullable();
        $table->json('best_time')->nullable();
        $table->json('dress_code')->nullable();
        $table->json('accessibility')->nullable();
        $table->boolean('is_featured')->default(false);
        $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->index(['destination_id', 'place_category_id', 'status']);
        $table->index(['status', 'updated_at']);
        $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
