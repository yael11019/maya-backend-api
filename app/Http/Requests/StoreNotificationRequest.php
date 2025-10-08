<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => 'nullable|exists:pets,id',
            'post_id' => 'nullable|exists:social_posts,id',
            'like_id' => 'nullable|exists:post_likes,id',
            'follower_ids' => 'nullable|array',
            'type' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ];
    }
}
