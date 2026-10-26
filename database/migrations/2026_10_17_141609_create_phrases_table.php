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
        Schema::create('phrases', function (Blueprint $table) {
            $table->id();
        $table->foreignId('phrase_category_id')->constrained('phrase_categories')->restrictOnDelete();
        $table->string('khmer_text');
        $table->string('romanization')->nullable();
        $table->json('translations');
        $table->boolean('is_emergency')->default(false);
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
        Schema::dropIfExists('phrases');
    }
};
