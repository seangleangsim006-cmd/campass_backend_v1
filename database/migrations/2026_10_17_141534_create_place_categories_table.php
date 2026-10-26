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
        Schema::create('place_categories', function (Blueprint $table) {
            $table->id();
        $table->json('name');
        $table->string('slug', 120)->unique();
        $table->enum('group', ['culture', 'beach', 'nature'])->nullable();
        $table->string('color', 9)->nullable();
        $table->string('icon', 60)->nullable();
        $table->integer('sort_order')->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_categories');
    }
};
