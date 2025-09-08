<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VaccineCollection extends ResourceCollection
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
            "data" => VaccineResource::collection($this->collection),
        ];
    }

}
