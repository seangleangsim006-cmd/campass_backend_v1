<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class DestinationResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'name'             => $this->tr($request, 'name'),
            'tagline'          => $this->tr($request, 'tagline'),
            'description'      => $this->tr($request, 'description'),
            'best_season'      => $this->tr($request, 'best_season'),
            'how_to_get_there' => $this->tr($request, 'how_to_get_there'),
            'latitude'         => $this->num($this->latitude),
            'longitude'        => $this->num($this->longitude),
            'is_premium'       => (bool) $this->is_premium,
            'version'          => $this->version,   // the app compares this to know "update available"
            'cover_url'        => $this->mediaUrl('cover'),
            'gallery'          => $this->galleryUrls(),
            'region'           => LookupResource::make($this->whenLoaded('region')),
            'places_count'     => $this->whenCounted('places'),
            'places'           => PlaceResource::collection($this->whenLoaded('places')),
            'emergency_contacts' => EmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),
            'updated_at'       => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}