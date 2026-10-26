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
        Schema::create('place_tips', function (Blueprint $table) {
           $table->id();
        $table->foreignId('place_id')->constrained()->cascadeOnDelete();
        $table->enum('type', ['etiquette', 'safety', 'scam', 'money', 'photo']);
        $table->json('text');
        $table->integer('sort_order')->default(0);
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_tips');
    }
};
