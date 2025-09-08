<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pet_id',
        'veterinarian_id',
        'title',
        'description',
        'appointment_date',
        'veterinarian_name',
        'clinic_name',
        'clinic_address',
        'phone',
        'status',
        'urgency',
        'appointment_type',
        'cost',
        'notes',
        'diagnosis',
        'treatment',
        'next_steps',
        'completed_at'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2'
    ];

    protected $appends = [
        'is_past_due',
        'time_until_appointment',
        'formatted_date'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'veterinarian_id', 'veterinarianId');
    }

    // Accessors
    public function getIsPastDueAttribute()
    {
        return $this->status === 'pending' && $this->appointment_date->isPast();
    }

    public function getTimeUntilAppointmentAttribute()
    {
        if ($this->status !== 'pending') {
            return null;
        }

        $now = Carbon::now();
        $appointment = $this->appointment_date;

        if ($appointment->isPast()) {
            return 'Overdue';
        }

        $diff = $now->diff($appointment);
        
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('M d, Y \a\t H:i');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPet($query, $petId)
    {
        return $query->where('pet_id', $petId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>', Carbon::now())
                    ->where('status', 'pending')
                    ->orderBy('appointment_date', 'asc');
    }

    public function scopeByUrgency($query, $urgency)
    {
        return $query->where('urgency', $urgency);
    }

    // Métodos
    public function markAsCompleted($data = [])
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => Carbon::now()
        ], $data));
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }
}
