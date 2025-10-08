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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_pet_id')->nullable(); // mascota que hizo la acción
            $table->unsignedBigInteger('target_pet_id')->nullable(); // mascota que recibió la acción
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('like_id')->nullable();
            $table->json('follower_ids')->nullable();
            $table->string('type'); // 'comment', 'like', 'follower'
            $table->unsignedBigInteger('user_id')->nullable(); // quien generó la notificación
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
