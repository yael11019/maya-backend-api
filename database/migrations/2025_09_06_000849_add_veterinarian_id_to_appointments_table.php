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
        Schema::table('appointments', function (Blueprint $table) {
            // Agregar la columna veterinarian_id después de pet_id
            $table->unsignedBigInteger('veterinarian_id')->nullable()->after('pet_id');
            
            // Crear la foreign key constraint
            $table->foreign('veterinarian_id')
                  ->references('veterinarianId')
                  ->on('veterinarians')
                  ->onDelete('set null');
                  
            // Crear índice para mejorar performance
            $table->index('veterinarian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Eliminar foreign key y columna
            $table->dropForeign(['veterinarian_id']);
            $table->dropIndex(['veterinarian_id']);
            $table->dropColumn('veterinarian_id');
        });
    }
};
