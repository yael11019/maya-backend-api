<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteVeterinarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'name',
        'address',
        'phone',
        'rating',
        'total_ratings',
        'business_status',
        'latitude',
        'longitude',
        'photo_url',
        'types'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'total_ratings' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}