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
        Schema::create('events', function (Blueprint $table) {
             $table->id();
        $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
        $table->json('name');
        $table->json('description')->nullable();
        $table->date('starts_on');
        $table->date('ends_on')->nullable();
        $table->boolean('is_recurring_yearly')->default(false);
        $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
        $table->timestamps();
        $table->softDeletes();

        $table->index(['starts_on', 'ends_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
