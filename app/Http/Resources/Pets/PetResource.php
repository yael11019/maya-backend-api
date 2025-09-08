<?php

namespace App\Http\Resources\Pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array
     * 
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "species" => $this->resource->species,
            "color" => $this->resource->color,
            "weight" => $this->resource->weight,
            "gender" => $this->resource->gender,
            "avatar" => $this->resource->avatar_url ?: $this->resource->avatar,
            "name" => $this->resource->name,
            "age" => $this->obtainAgeFromBirthdate(),
            "birthdate" => $this->resource->birth_date,
            "breed" => $this->resource->breed,
            "sterilized" => $this->resource->sterilized,
            "medical_conditions" => $this->resource->medical_conditions,
            "distinctive_traits" => $this->resource->distinctive_traits
        ];
    }

    public function obtainAgeFromBirthdate(): int
    {
        return Carbon::parse($this->resource->birth_date)->age;
    }
}
