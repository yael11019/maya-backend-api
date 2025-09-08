<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            // Si ya tienes una columna vaccine_image, renómbrala
            if (Schema::hasColumn('vaccinations', 'vaccine_image')) {
                $table->renameColumn('vaccine_image', 'vaccineImage');
            }
            // Si no existe ninguna columna de imagen, créala
            elseif (!Schema::hasColumn('vaccinations', 'vaccineImage')) {
                $table->string('vaccineImage')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            if (Schema::hasColumn('vaccinations', 'vaccineImage')) {
                $table->renameColumn('vaccineImage', 'vaccine_image');
            }
        });
    }
};