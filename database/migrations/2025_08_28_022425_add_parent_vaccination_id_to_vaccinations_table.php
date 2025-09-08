<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_vaccination_id')->nullable()->after('vaccineImage');
            $table->foreign('parent_vaccination_id')->references('vaccinationId')->on('vaccinations')->onDelete('set null');
            $table->index(['petId', 'vaccineStatus', 'vaccineNextDate']); // Índice para consultas
        });
    }

    public function down()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropForeign(['parent_vaccination_id']);
            $table->dropColumn('parent_vaccination_id');
        });
    }
};