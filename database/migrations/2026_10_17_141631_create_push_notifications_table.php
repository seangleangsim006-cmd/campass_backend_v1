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
        Schema::create('push_notifications', function (Blueprint $table) {
             $table->id();
        $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
        $table->json('title');
        $table->json('message');
        $table->json('audience')->nullable();
        $table->timestamp('scheduled_at')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->unsignedInteger('sent_count')->default(0);
        $table->unsignedInteger('opened_count')->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
