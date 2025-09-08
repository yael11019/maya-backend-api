<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('favorite_veterinarians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('place_id')->unique(); // Google Places ID
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->integer('total_ratings')->default(0);
            $table->string('business_status')->default('UNKNOWN');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('photo_url')->nullable();
            $table->text('types')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'place_id']);
            $table->unique(['user_id', 'place_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorite_veterinarians');
    }
};