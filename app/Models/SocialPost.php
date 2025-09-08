<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPost extends Model
{
    protected $fillable = [
        'pet_id',
        'user_id',
        'caption',
        'media_url',
        'media_type',
        'mime_type',
        'likes_count',
        'comments_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class, 'post_id')->where('is_active', true);
    }

    // Helper method to check if user liked the post
    public function isLikedBy($userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    // Scope for active posts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for posts from followed pets
    public function scopeFromFollowedPets($query, $userId)
    {
        return $query->whereIn('pet_id', function($subQuery) use ($userId) {
            $subQuery->select('followed_pet_id')
                     ->from('pet_followers')
                     ->where('follower_user_id', $userId);
        });
    }
}
