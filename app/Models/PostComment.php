<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostComment extends Model
{
    protected $fillable = [
        'post_id',
        'pet_id',
        'comment',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'post_id');
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    // Scope for active comments
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
