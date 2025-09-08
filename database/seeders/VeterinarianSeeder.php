<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Veterinarian;

class VeterinarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $veterinarians = [
            [
                'veterinarianName' => 'Dr. María González',
                'email' => 'maria.gonzalez@clinicaveterinaria.com',
                'phone' => '+34 91 123 4567',
                'streetName' => 'Av. Principal',
                'streetNumber' => '123',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zipCode' => '28001',
                'clinicName' => 'Clínica Veterinaria Central'
            ],
            [
                'veterinarianName' => 'Dr. Carlos Ruiz',
                'email' => 'carlos.ruiz@veterinarialosamigos.com',
                'phone' => '+34 91 987 6543',
                'streetName' => 'Calle Secundaria',
                'streetNumber' => '456',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zipCode' => '28002',
                'clinicName' => 'Veterinaria Los Amigos'
            ],
            [
                'veterinarianName' => 'Dra. Ana López',
                'email' => 'ana.lopez@hospital24h.com',
                'phone' => '+34 91 555 0123',
                'streetName' => 'Plaza Mayor',
                'streetNumber' => '789',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zipCode' => '28003',
                'clinicName' => 'Hospital Veterinario 24h'
            ],
            [
                'veterinarianName' => 'Dr. Miguel Torres',
                'email' => 'miguel.torres@clinicadental.com',
                'phone' => '+34 91 444 7890',
                'streetName' => 'Av. Veterinaria',
                'streetNumber' => '321',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zipCode' => '28004',
                'clinicName' => 'Clínica Dental Veterinaria'
            ],
            [
                'veterinarianName' => 'Dra. Carmen Fernández',
                'email' => 'carmen.fernandez@vetexotic.com',
                'phone' => '+34 91 333 2468',
                'streetName' => 'Calle Exóticos',
                'streetNumber' => '159',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'zipCode' => '28005',
                'clinicName' => 'Veterinaria Exóticos'
            ]
        ];

        foreach ($veterinarians as $veterinarian) {
            Veterinarian::create($veterinarian);
        }

        $this->command->info('Created ' . count($veterinarians) . ' veterinarians.');
    }
}
