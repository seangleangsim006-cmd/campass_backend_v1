<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class ItineraryItemResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'place_id'        => $this->place_id,   // the source of truth when syncing offline
            'day_number'      => $this->day_number,
            'sort_order'      => $this->sort_order,
            'time_suggestion' => $this->time_suggestion,
            'note'            => $this->tr($request, 'note'),
            'place'           => $this->whenLoaded('place', fn ($place) => [
                'id'        => $place->id,
                'slug'      => $place->slug,
                'name'      => $this->tr($request, 'name', $place),
                'latitude'  => $this->num($place->latitude),
                'longitude' => $this->num($place->longitude),
                'cover_url' => $place->getFirstMediaUrl('cover') ?: null,
            ]),
        ];
    }
}