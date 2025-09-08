<?php

namespace App\Http\Resources\Pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PetCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into array
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            "data" => PetResource::collection($this->collection),
        ];
    }

}
