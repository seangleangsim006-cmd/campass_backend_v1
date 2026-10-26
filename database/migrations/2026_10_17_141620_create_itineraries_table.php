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
        Schema::create('itineraries', function (Blueprint $table) {
             $table->id();
        $table->foreignId('destination_id')->constrained()->restrictOnDelete();
        $table->json('title');
        $table->string('slug', 150)->unique();
        $table->json('description')->nullable();
        $table->unsignedTinyInteger('days_count');
        $table->decimal('budget_estimate', 8, 2)->nullable();
        $table->boolean('is_premium')->default(false);
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
        Schema::dropIfExists('itineraries');
    }
};
