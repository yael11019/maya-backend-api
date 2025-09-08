<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Vaccination extends Model
{
    use HasFactory;

    protected $primaryKey = 'vaccinationId';

    protected $fillable = [
        'petId',
        'veterinarianId',
        'vaccinationDate',
        'vaccineName',
        'vaccineLot',
        'vaccineNextDate',
        'vaccineStatus',
        'vaccineType',
        'notes',
        'vaccineImage',
        'parent_vaccination_id'
    ];

    protected $casts = [
        'vaccinationDate' => 'date',
        'vaccineNextDate' => 'date',
        'notes' => 'array',
    ];

    /**
     * Relación con Pet
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'petId', 'id');
    }

    /**
     * Relación con Veterinarian
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class, 'veterinarianId', 'veterinarianId');
    }

    /**
     * Verificar si la vacuna está atrasada
     */
    public function isOverdue(): bool
    {
        if (!$this->vaccineNextDate || $this->vaccineStatus === 'completed') {
            return false;
        }
        
        return Carbon::parse($this->vaccineNextDate)->isPast();
    }

    /**
     * Días hasta la próxima vacuna
     */
    public function daysUntilDue(): ?int
    {
        if (!$this->vaccineNextDate) {
            return null;
        }
        
        return Carbon::now()->diffInDays(Carbon::parse($this->vaccineNextDate), false);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('vaccineStatus', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('vaccineStatus', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('vaccineStatus', 'pending')
                    ->where('vaccineNextDate', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('vaccineStatus', 'pending')
                    ->where('vaccineNextDate', '>=', now());
    }

    public function getVaccineImageUrlAttribute(): ?string
    {
        if ($this->vaccineImage) {
            return asset('storage/' . $this->vaccineImage);
        }
        return null;
    }

    public function parentVaccination()
    {
        return $this->belongsTo(Vaccination::class, 'parent_vaccination_id', 'vaccinationId');
    }

    // Relación con las vacunas hijas
    public function childVaccinations()
    {
        return $this->hasMany(Vaccination::class, 'parent_vaccination_id', 'vaccinationId');
    }
}