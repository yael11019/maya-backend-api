<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_pet_id', // mascota que hizo la acción
        'target_pet_id', // mascota que recibió la acción
        'post_id',
        'like_id',
        'follower_ids',
        'type',
        'user_id',
        'message',
    ];

    protected $casts = [
        'follower_ids' => 'array',
    ];
}
