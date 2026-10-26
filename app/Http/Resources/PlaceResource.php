<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

use App\Http\Resources\Concerns\TransformsContent;
use App\Support\Premium;

class PlaceResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        // stories of a premium destination are locked unless the user has access
        $locked = $this->resource->relationLoaded('destination')
            && (bool) $this->destination?->is_premium
            && ! Premium::allows($request->user('sanctum'));

        return [
            'id'                     => $this->id,
            'slug'                   => $this->slug,
            'destination_id'         => $this->destination_id,
            'category_id'            => $this->place_category_id,
            'name'                   => $this->tr($request, 'name'),
            'description'            => $this->tr($request, 'description'),
            'latitude'               => $this->num($this->latitude),
            'longitude'              => $this->num($this->longitude),
            'radius_m'               => $this->radius_m,
            'opening_hours'          => $this->opening_hours,
            'entrance_fee'           => $this->num($this->entrance_fee),
            'fee_note'               => $this->tr($request, 'fee_note'),
            'suggested_duration_min' => $this->suggested_duration_min,
            'best_time'              => $this->tr($request, 'best_time'),
            'dress_code'             => $this->tr($request, 'dress_code'),
            'accessibility'          => $this->tr($request, 'accessibility'),
            'is_featured'            => (bool) $this->is_featured,
            'cover_url'              => $this->mediaUrl('cover'),
            'gallery'                => $this->galleryUrls(),

            'category'    => LookupResource::make($this->whenLoaded('category')),
            'destination' => $this->whenLoaded('destination', fn () => [
                'id'         => $this->destination->id,
                'slug'       => $this->destination->slug,
                'name'       => $this->tr($request, 'name', $this->destination),
                'is_premium' => (bool) $this->destination->is_premium,
            ]),
            'locked'      => $this->when($this->resource->relationLoaded('destination'), $locked),

            'tips'    => PlaceTipResource::collection($this->whenLoaded('tips')),
            'stories' => $this->whenLoaded('stories', fn () => $this->stories->map(
                fn ($story) => (new PlaceStoryResource($story))->locked($locked)->resolve($request)
            )->all()),

            // only present on some queries
            'has_story'   => $this->when($this->resource->getAttribute('has_story') !== null,
                                fn () => (bool) $this->has_story),
            'distance_km' => $this->when($this->resource->getAttribute('distance_km') !== null,
                                fn () => round((float) $this->distance_km, 2)),
            'saved_at'    => $this->when($this->resource->getAttribute('saved_at') !== null,
                                fn () => Carbon::parse($this->saved_at)->toIso8601ZuluString()),
             'visited_at'  => $this->when($this->resource->getAttribute('visited_at') !== null,
                    fn () => Carbon::parse($this->visited_at)->toIso8601ZuluString()),                   

            'updated_at'  => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}