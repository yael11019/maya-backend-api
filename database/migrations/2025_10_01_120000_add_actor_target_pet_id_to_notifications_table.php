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
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'actor_pet_id')) {
                $table->unsignedBigInteger('actor_pet_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('notifications', 'target_pet_id')) {
                $table->unsignedBigInteger('target_pet_id')->nullable()->after('actor_pet_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'actor_pet_id')) {
                $table->dropColumn('actor_pet_id');
            }
            if (Schema::hasColumn('notifications', 'target_pet_id')) {
                $table->dropColumn('target_pet_id');
            }
        });
    }
};
