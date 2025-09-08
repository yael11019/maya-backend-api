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
        Schema::create('pet_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('followed_pet_id')->constrained('pets')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['follower_user_id', 'followed_pet_id']);
            $table->index(['followed_pet_id']);
            $table->index(['follower_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_followers');
    }
};
