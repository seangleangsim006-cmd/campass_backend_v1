<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;
use App\Models\PlaceCategory;

class LookupResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'slug' => $this->slug,
            'name' => $this->tr($request, 'name'),
            $this->mergeWhen($this->resource instanceof PlaceCategory, fn () => [
                'group' => $this->group,
                'color' => $this->color,
                'icon'  => $this->icon,
            ]),
        ];
    }
}