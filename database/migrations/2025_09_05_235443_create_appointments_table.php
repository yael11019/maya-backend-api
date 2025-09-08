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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pet_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('appointment_date');
            $table->string('veterinarian_name');
            $table->string('clinic_name');
            $table->text('clinic_address')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('urgency', ['low', 'medium', 'high'])->default('medium');
            $table->enum('appointment_type', ['consultation', 'vaccination', 'surgery', 'emergency', 'checkup', 'other'])->default('consultation');
            $table->decimal('cost', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('next_steps')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['user_id', 'appointment_date']);
            $table->index(['pet_id', 'status']);
            $table->index('appointment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
