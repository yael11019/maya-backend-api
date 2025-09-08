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
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id('vaccinationId');
            $table->foreignId('petId')->constrained('pets', 'id')->onDelete('cascade'); // ← pets.id no pets.petId
            $table->foreignId('veterinarianId')->constrained('veterinarians', 'veterinarianId')->onDelete('cascade'); // ← veterinarians.veterinarianId
            $table->date('vaccinationDate')->nullable();
            $table->string('vaccineName');
            $table->string('vaccineLot')->nullable();
            $table->string('vaccineNextDate')->nullable();
            $table->enum('vaccineStatus', ['pending', 'completed'])->default('pending');
            $table->enum('vaccineType', ['vaccine', 'desparasitante'])->default('vaccine');
            $table->json('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
