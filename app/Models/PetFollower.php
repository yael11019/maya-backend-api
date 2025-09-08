<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetFollower extends Model
{
    protected $fillable = [
        'follower_user_id',
        'followed_pet_id'
    ];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_user_id');
    }

    public function followedPet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'followed_pet_id');
    }
}
