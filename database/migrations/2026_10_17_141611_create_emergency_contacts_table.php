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
        Schema::create('emergency_contacts', function (Blueprint $table) {
           $table->id();
        $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('type', ['tourist_police', 'police', 'ambulance', 'hospital', 'embassy', 'other']);
        $table->json('name');
        $table->string('phone', 40);
        $table->json('address')->nullable();
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
