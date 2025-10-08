<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaccineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->vaccinationId,
            'vaccination_id' => $this->vaccinationId,
            'vaccine_name' => $this->vaccineName,
            'vaccination_date' => $this->vaccinationDate?->format('Y-m-d'),
            'next_vaccination_date' => $this->vaccineNextDate?->format('Y-m-d'),
            'vaccine_lot' => $this->vaccineLot,
            'status' => $this->vaccineStatus,
            'type' => $this->vaccineType,
            'notes' => $this->notes,
            'vaccine_image' => $this->vaccineImage ? asset('storage/' . $this->vaccineImage) : null, // Cambiar campo
            'pet_id' => $this->petId,
            'veterinarian_id' => $this->veterinarianId,
            'veterinarian' => $this->whenLoaded('veterinarian', function () {
                return [
                    'id' => $this->veterinarian->id,
                    'name' => $this->veterinarian->name,
                    'clinic_name' => $this->veterinarian->clinic_name,
                    'contact_info' => $this->veterinarian->contact_info,
                ];
            }),
            'pet' => $this->whenLoaded('pet', function () {
                return [
                    'id' => $this->pet->id,
                    'name' => $this->pet->name,
                    'species' => $this->pet->species,
                ];
            }),
            'is_overdue' => $this->isOverdue(),
            'days_until_due' => $this->daysUntilDue(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}