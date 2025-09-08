<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinarian extends Model
{
    use HasFactory;

    protected $table = 'veterinarians';
    protected $primaryKey = 'veterinarianId';

    protected $fillable = [
        'veterinarianName',
        'email',
        'phone',
        'streetName',
        'streetNumber',
        'city',
        'state',
        'zipCode',
        'notes',
        'clinicName'
    ];

    protected $casts = [
        'notes' => 'array'
    ];

    /**
     * Relación con Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'veterinarian_id', 'veterinarianId');
    }

    /**
     * Relación con Vaccinations
     */
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'veterinarianId', 'veterinarianId');
    }

    /**
     * Obtener la dirección completa del veterinario
     */
    public function getFullAddressAttribute()
    {
        $address = [];
        
        if ($this->streetName) {
            $streetPart = $this->streetName;
            if ($this->streetNumber) {
                $streetPart = $this->streetNumber . ' ' . $streetPart;
            }
            $address[] = $streetPart;
        }
        
        if ($this->city) {
            $address[] = $this->city;
        }
        
        if ($this->state) {
            $address[] = $this->state;
        }
        
        if ($this->zipCode) {
            $address[] = $this->zipCode;
        }
        
        return implode(', ', $address);
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeByName($query, $name)
    {
        return $query->where('veterinarianName', 'like', '%' . $name . '%');
    }

    /**
     * Scope para buscar por ciudad
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }
}
