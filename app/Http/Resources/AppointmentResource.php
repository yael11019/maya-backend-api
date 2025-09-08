<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pet' => [
                'id' => $this->pet->id,
                'name' => $this->pet->name,
                'species' => $this->pet->species,
                'breed' => $this->pet->breed,
            ],
            'veterinarian' => $this->when($this->veterinarian, [
                'id' => $this->veterinarian?->veterinarianId,
                'name' => $this->veterinarian?->veterinarianName,
                'clinic_name' => $this->veterinarian?->clinicName,
                'phone' => $this->veterinarian?->phone,
                'email' => $this->veterinarian?->email,
                'full_address' => $this->veterinarian?->full_address,
            ]),
            'title' => $this->title,
            'description' => $this->description,
            'appointment_date' => $this->appointment_date?->format('Y-m-d H:i:s'),
            'appointment_date_formatted' => $this->appointment_date?->format('d/m/Y H:i'),
            'veterinarian_name' => $this->veterinarian_name,
            'clinic_name' => $this->clinic_name,
            'clinic_address' => $this->clinic_address,
            'phone' => $this->phone,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'urgency' => $this->urgency,
            'urgency_label' => $this->getUrgencyLabel(),
            'appointment_type' => $this->appointment_type,
            'cost' => $this->cost,
            'notes' => $this->notes,
            'diagnosis' => $this->diagnosis,
            'treatment' => $this->treatment,
            'next_steps' => $this->next_steps,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get human readable status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
            default => 'Desconocido'
        };
    }

    /**
     * Get human readable urgency label
     */
    private function getUrgencyLabel(): string
    {
        return match($this->urgency) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            default => 'Sin especificar'
        };
    }
}
