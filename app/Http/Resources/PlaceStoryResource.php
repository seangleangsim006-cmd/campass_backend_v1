<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Middleware\SetApiLocale;
use App\Http\Resources\Concerns\TransformsContent;

class PlaceStoryResource extends JsonResource
{
    use TransformsContent;

    protected bool $locked = false;

    public function locked(bool $locked = true): static
    {
        $this->locked = $locked;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $audio = collect(SetApiLocale::SUPPORTED)
            ->mapWithKeys(fn ($lang) => [$lang => $this->mediaUrl("audio_{$lang}")])
            ->filter()
            ->all();

        return [
            'id'                  => $this->id,
            'place_id'            => $this->place_id,
            'title'               => $this->tr($request, 'title'),
            'transcript'          => $this->locked ? null : $this->tr($request, 'transcript'),
            'duration_sec'        => $this->duration_sec,
            'autoplay_on_arrival' => (bool) $this->autoplay_on_arrival,
            'sort_order'          => $this->sort_order,
            'locked'              => $this->locked,
            'audio'               => $this->locked ? null : ($audio ?: new \stdClass),   // {"en": url, "km": url}
            'updated_at'          => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}