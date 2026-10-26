<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->uuid,
            'name'               => $this->name,
            'email'              => $this->email,
            'avatar'             => $this->avatar,
            'country_code'       => $this->country_code,
            'preferred_language' => $this->preferred_language,
            'roles'              => $this->getRoleNames(),
            'created_at'         => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
