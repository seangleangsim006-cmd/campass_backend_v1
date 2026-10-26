<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class TransportRouteResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'mode'         => $this->mode,
            'from_id'      => $this->from_destination_id,
            'to_id'        => $this->to_destination_id,
            'from'         => $this->whenLoaded('fromDestination', fn ($d) => [
                'id'   => $d->id,
                'slug' => $d->slug,
                'name' => $this->tr($request, 'name', $d),
            ]),
            'to'           => $this->whenLoaded('toDestination', fn ($d) => [
                'id'   => $d->id,
                'slug' => $d->slug,
                'name' => $this->tr($request, 'name', $d),
            ]),
            'duration_min' => $this->duration_min,
            'price_min'    => $this->num($this->price_min),
            'price_max'    => $this->num($this->price_max),
            'currency'     => $this->currency,
            'notes'        => $this->tr($request, 'notes'),
            'updated_at'   => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}