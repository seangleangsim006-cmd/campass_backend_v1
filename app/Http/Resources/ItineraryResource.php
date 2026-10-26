<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;
use App\Support\Premium;

class ItineraryResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        $destination = $this->resource->relationLoaded('destination') ? $this->destination : null;

        // premium itinerary, or any itinerary inside a premium destination
        $locked = ((bool) $this->is_premium || (bool) $destination?->is_premium)
            && ! Premium::allows($request->user('sanctum'));

        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'destination_id'  => $this->destination_id,
            'destination'     => $this->whenLoaded('destination', fn () => [
                'id'         => $this->destination->id,
                'slug'       => $this->destination->slug,
                'name'       => $this->tr($request, 'name', $this->destination),
                'is_premium' => (bool) $this->destination->is_premium,
            ]),
            'title'           => $this->tr($request, 'title'),
            'description'     => $this->tr($request, 'description'),
            'days_count'      => $this->days_count,
            'budget_estimate' => $this->num($this->budget_estimate),
            'is_premium'      => (bool) $this->is_premium,
            'locked'          => $locked,
            'cover_url'       => $this->mediaUrl('cover'),
            'items_count'     => $this->whenCounted('items'),
            // a locked itinerary never leaks its stops
            'items'           => $locked ? [] : ItineraryItemResource::collection($this->whenLoaded('items')),
            'updated_at'      => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}