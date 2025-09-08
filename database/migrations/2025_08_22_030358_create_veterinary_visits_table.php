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
        Schema::create('veterinaryVisits', function (Blueprint $table) {
            $table->id('veterinaryVisitsId');
            $table->foreignId('petId')->constrained('pets', 'id')->onDelete('cascade'); // ← pets.id no pets.petId
            $table->foreignId('veterinarianId')->constrained('veterinarians', 'veterinarianId')->onDelete('cascade'); // ← veterinarians.veterinarianId
            $table->date('visitDate');
            $table->string('reason');
            $table->text('diagnosis')->nullable();
            $table->text('typeOfVisit')->nullable();
            $table->text('treatment')->nullable();
            $table->string('documents')->nullable();
            $table->string('state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinary_visits');
    }
};
