<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Pet;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user that has pets
        $pets = Pet::with('user')->get();
        if ($pets->isEmpty()) {
            $this->command->warn('No pets found. Please create some pets first.');
            return;
        }

        $user = $pets->first()->user;
        $userPets = Pet::where('user_id', $user->id)->get();

        $appointmentData = [
            [
                'user_id' => $user->id,
                'pet_id' => $userPets->first()->id,
                'title' => 'Chequeo General',
                'description' => 'Revisión médica anual completa',
                'appointment_date' => Carbon::now()->addDays(7)->setTime(10, 0),
                'veterinarian_name' => 'Dr. María González',
                'clinic_name' => 'Clínica Veterinaria Central',
                'clinic_address' => 'Av. Principal 123, Ciudad',
                'phone' => '+34 91 123 4567',
                'status' => 'pending',
                'urgency' => 'low',
                'appointment_type' => 'checkup',
                'cost' => 45.00,
                'notes' => 'Primera consulta del año'
            ],
            [
                'user_id' => $user->id,
                'pet_id' => $userPets->first()->id,
                'title' => 'Vacunación',
                'description' => 'Vacuna anual obligatoria',
                'appointment_date' => Carbon::now()->addDays(14)->setTime(15, 30),
                'veterinarian_name' => 'Dr. Carlos Ruiz',
                'clinic_name' => 'Veterinaria Los Amigos',
                'clinic_address' => 'Calle Secundaria 456, Ciudad',
                'phone' => '+34 91 987 6543',
                'status' => 'pending',
                'urgency' => 'medium',
                'appointment_type' => 'vaccination',
                'cost' => 25.00,
                'notes' => 'Traer cartilla de vacunación'
            ],
            [
                'user_id' => $user->id,
                'pet_id' => $userPets->count() > 1 ? $userPets->get(1)->id : $userPets->first()->id,
                'title' => 'Urgencia - Malestar Estomacal',
                'description' => 'La mascota presenta vómitos y diarrea',
                'appointment_date' => Carbon::now()->addHours(2),
                'veterinarian_name' => 'Dra. Ana López',
                'clinic_name' => 'Hospital Veterinario 24h',
                'clinic_address' => 'Plaza Mayor 789, Ciudad',
                'phone' => '+34 91 555 0123',
                'status' => 'pending',
                'urgency' => 'high',
                'appointment_type' => 'emergency',
                'cost' => 80.00,
                'notes' => 'Síntomas desde ayer por la noche'
            ],
            [
                'user_id' => $user->id,
                'pet_id' => $userPets->first()->id,
                'title' => 'Limpieza Dental',
                'description' => 'Limpieza y revisión dental completa',
                'appointment_date' => Carbon::now()->subDays(5)->setTime(9, 0),
                'veterinarian_name' => 'Dr. Miguel Torres',
                'clinic_name' => 'Clínica Dental Veterinaria',
                'clinic_address' => 'Av. Veterinaria 321, Ciudad',
                'phone' => '+34 91 444 7890',
                'status' => 'completed',
                'urgency' => 'low',
                'appointment_type' => 'other',
                'cost' => 120.00,
                'notes' => 'Recomendado cepillado diario',
                'diagnosis' => 'Sarro moderado en molares',
                'treatment' => 'Limpieza ultrasónica y pulido dental',
                'next_steps' => 'Revisión en 6 meses, cepillado diario',
                'completed_at' => Carbon::now()->subDays(5)->setTime(10, 30)
            ]
        ];

        foreach ($appointmentData as $data) {
            Appointment::create($data);
        }

        $this->command->info('Created ' . count($appointmentData) . ' sample appointments.');
    }
}
