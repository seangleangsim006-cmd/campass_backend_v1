<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class EmergencyContactResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'name'             => $this->tr($request, 'name'),
            'phone'            => $this->phone,
            'address'          => $this->tr($request, 'address'),
            'destination_id'   => $this->destination_id,   // null = nationwide
            'destination_slug' => $this->whenLoaded('destination', fn () => $this->destination?->slug),
            'updated_at'       => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}