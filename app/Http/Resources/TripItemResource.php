<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class TripItemResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->uuid,
            'place_id'   => $this->place_id,
            'day_number' => $this->day_number,
            'sort_order' => $this->sort_order,
            'note'       => $this->note,
            // null when the place was deleted or unpublished
            'place'      => $this->whenLoaded('place', fn ($place) => [
                'id'        => $place->id,
                'slug'      => $place->slug,
                'name'      => $this->tr($request, 'name', $place),
                'latitude'  => $this->num($place->latitude),
                'longitude' => $this->num($place->longitude),
                'cover_url' => $place->getFirstMediaUrl('cover') ?: null,
            ]),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}