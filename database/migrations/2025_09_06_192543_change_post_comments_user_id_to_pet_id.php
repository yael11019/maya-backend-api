<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            // Eliminar la foreign key constraint existente si existe
            $table->dropForeign(['user_id']);
        });

        // Actualizar los datos existentes: mapear user_id a pet_id
        // Para cada comentario, encontrar la primera mascota del usuario
        DB::statement('
            UPDATE post_comments 
            SET user_id = (
                SELECT pets.id 
                FROM pets 
                WHERE pets.user_id = post_comments.user_id 
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 
                FROM pets 
                WHERE pets.user_id = post_comments.user_id
            )
        ');

        // Eliminar comentarios de usuarios que no tienen mascotas
        DB::statement('
            DELETE FROM post_comments 
            WHERE user_id NOT IN (
                SELECT DISTINCT user_id FROM pets
            )
        ');

        Schema::table('post_comments', function (Blueprint $table) {
            // Renombrar la columna user_id a pet_id
            $table->renameColumn('user_id', 'pet_id');
            
            // Agregar la nueva foreign key constraint
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            // Eliminar la foreign key constraint de pet_id
            $table->dropForeign(['pet_id']);
            
            // Renombrar la columna pet_id de vuelta a user_id
            $table->renameColumn('pet_id', 'user_id');
        });

        // Actualizar los datos de vuelta: mapear pet_id a user_id del dueño de la mascota
        DB::statement('
            UPDATE post_comments 
            SET user_id = (
                SELECT pets.user_id 
                FROM pets 
                WHERE pets.id = post_comments.user_id
            )
        ');

        Schema::table('post_comments', function (Blueprint $table) {
            // Restaurar la foreign key constraint original
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
