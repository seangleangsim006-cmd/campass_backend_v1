<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

use App\Models\{Destination, Event, Itinerary, Place, PlaceStory, PlaceTip, TransportRoute};

class BumpDestinationVersion
{
    public function saved(Model $model): void    { $this->bump($model); }
    public function deleted(Model $model): void  { $this->bump($model); }
    public function restored(Model $model): void { $this->bump($model); }

    private function bump(Model $model): void
    {
        $ids = match (true) {
            $model instanceof Place,
            $model instanceof Itinerary,
            $model instanceof Event => [$model->destination_id],

            $model instanceof PlaceStory,
            $model instanceof PlaceTip => [
                Place::withTrashed()->whereKey($model->place_id)->value('destination_id'),
            ],

            $model instanceof TransportRoute => [$model->from_destination_id, $model->to_destination_id],

            default => [],
        };

        $ids = array_values(array_filter(array_unique($ids)));

        if ($ids) {
            // increment() also updates updated_at, which /sync relies on
            Destination::whereKey($ids)->increment('version');
        }
    }
}