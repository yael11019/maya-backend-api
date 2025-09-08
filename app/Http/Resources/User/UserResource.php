<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'surname' => $this->resource->surname,
            'full_name' => $this->resource->name . ' ' . $this->resource->surname,
            'gender' => $this->resource->gender,
            'role_id' => $this->resource->role_id,
            'role' => [
                'name' => env("APP_URL") .'storage'.$this->resource->role->name,
            ],
            'role_name' => $this->resource->role->name,
            'avatar' => $this->resource->avatar ? env("APP_URL") . 'storage/' . $this->resource->avatar : null,
            'document_type' => $this->resource->document_type,
            'document_number' => $this->resource->document_number,
            'phone' => $this->resource->phone,
            'designation' => $this->resource->designation,
            'birthday' => $this->resource->birthday ? Carbon::parse($this->resource->birthday)->format('Y/m/d') : null,
        ];
    }
}
