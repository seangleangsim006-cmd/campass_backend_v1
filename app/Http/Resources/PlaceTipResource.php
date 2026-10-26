<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class PlaceTipResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'place_id'   => $this->place_id,
            'type'       => $this->type,
            'text'       => $this->tr($request, 'text'),
            'sort_order' => $this->sort_order,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}