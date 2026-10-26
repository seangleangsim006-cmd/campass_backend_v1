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
        Schema::create('transport_routes', function (Blueprint $table) {
            
                $table->id();
                $table->foreignId('from_destination_id')->constrained('destinations')->cascadeOnDelete();
                $table->foreignId('to_destination_id')->constrained('destinations')->cascadeOnDelete();
                $table->enum('mode', ['bus', 'minivan', 'ferry', 'taxi', 'flight', 'train']);
                $table->unsignedInteger('duration_min')->nullable();
                $table->decimal('price_min', 8, 2)->nullable();
                $table->decimal('price_max', 8, 2)->nullable();
                $table->char('currency', 3)->default('USD');
                $table->json('notes')->nullable();
                $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['from_destination_id', 'to_destination_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_routes');
    }
};
