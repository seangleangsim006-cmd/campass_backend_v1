<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

use App\Http\Resources\Concerns\TransformsContent;

class EventResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        [$start, $end] = $this->occurrence();
        $today = Carbon::today();

        return [
            'id'                  => $this->id,
            'name'                => $this->tr($request, 'name'),
            'description'         => $this->tr($request, 'description'),
            'cover_url'           => $this->mediaUrl('cover'),   // null when there is no photo
            'gallery'             => $this->galleryUrls(),       // array of image URLs, in the order set in the admin
            // the dates the editor typed (an example year for yearly events)
            'starts_on'           => $this->starts_on?->toDateString(),
            'ends_on'             => $this->ends_on?->toDateString(),
            'is_recurring_yearly' => (bool) $this->is_recurring_yearly,
            // the current or next occurrence: show these dates in the app
            'next_starts_on'      => $start->toDateString(),
            'next_ends_on'        => $end->toDateString(),
            'is_ongoing'          => $start->lte($today) && $end->gte($today),
            'destination_id'      => $this->destination_id,   // null = nationwide
            'destination'         => $this->whenLoaded('destination', fn($d) => $d ? [
                'id'   => $d->id,
                'slug' => $d->slug,
                'name' => $this->tr($request, 'name', $d),
            ] : null),
            'updated_at'          => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
