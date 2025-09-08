<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'breed',
        'color',
        'weight',
        'age',
        'gender',
        'birth_date',
        'medical_conditions',
        'distinctive_traits',
        'avatar',
        'user_id',
        'sterilized',
        'microchip_number',
        'registration_number',
        'emergency_contact',
        'notes'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'sterilized' => 'boolean',
        'weight' => 'decimal:2',
        'age' => 'integer'
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con las vacunaciones
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'petId', 'id');
    }

    // Relación con las citas médicas
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Relaciones de red social
    public function socialPosts()
    {
        return $this->hasMany(SocialPost::class)->where('is_active', true);
    }

    public function followers()
    {
        return $this->hasMany(PetFollower::class, 'followed_pet_id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    // Helper methods for social features
    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function isFollowedBy($userId): bool
    {
        return $this->followers()->where('follower_user_id', $userId)->exists();
    }

    // Accessor para la imagen
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }
}