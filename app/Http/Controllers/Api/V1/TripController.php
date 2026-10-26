<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Models\{Place, Trip, TripItem};

class TripController extends Controller
{
    private const MAX_TRIPS = 50;

    public function index(Request $request)
    {
        $trips = $request->user()->trips()
            ->with('items.place.media')
            ->orderByDesc('updated_at')
            ->get();

        return TripResource::collection($trips);
    }

    public function show(Request $request, string $uuid)
    {
        $trip = $request->user()->trips()
            ->where('uuid', $uuid)
            ->with('items.place.media')
            ->firstOrFail();

        return new TripResource($trip);
    }

    /** PUT /me/trips/{uuid}: create or replace one trip and its items. Safe to retry. */
    public function upsert(Request $request, string $uuid)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'              => ['required', 'string', 'max:150'],
            'start_date'         => ['nullable', 'date'],
            'end_date'           => ['nullable', 'date', 'after_or_equal:start_date'],
            'items'              => ['present', 'array', 'max:200'],
            'items.*.id'         => ['required', 'uuid', 'distinct'],
            'items.*.place_id'   => ['required', 'integer'],
            'items.*.day_number' => ['required', 'integer', 'between:1,60'],
            'items.*.sort_order' => ['nullable', 'integer', 'between:0,10000'],
            'items.*.note'       => ['nullable', 'string', 'max:500'],
        ]);

        $trip = Trip::withTrashed()->where('uuid', $uuid)->first();

        // never reveal that the uuid belongs to someone else
        if ($trip && $trip->user_id !== $user->id) {
            abort(404);
        }

        if ($trip?->trashed()) {
            return response()->json([
                'message' => 'This trip was deleted.',
                'code'    => 'trip_deleted',
            ], 410);
        }

        if (! $trip && $user->trips()->count() >= self::MAX_TRIPS) {
            throw ValidationException::withMessages([
                'title' => ['You have reached the limit of ' . self::MAX_TRIPS . ' trips.'],
            ]);
        }

        // keep only places that are still published; report the rest
        $items     = collect($data['items']);
        $available = Place::visible()
            ->whereIn('places.id', $items->pluck('place_id')->unique()->all())
            ->pluck('places.id')
            ->all();

        $kept    = $items->whereIn('place_id', $available)->values();
        $skipped = $items->whereNotIn('place_id', $available)->pluck('id')->values();

        // an item id must not already belong to a different trip
        $conflict = TripItem::withTrashed()
            ->whereIn('uuid', $kept->pluck('id')->all())
            ->when($trip, fn ($q) => $q->where('trip_id', '!=', $trip->id))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'items' => ['One or more item ids are already in use.'],
            ]);
        }

        $created = $trip === null;

        DB::transaction(function () use (&$trip, $user, $uuid, $data, $kept) {
            $trip ??= new Trip(['uuid' => $uuid, 'user_id' => $user->id]);

            $trip->fill([
                'title'      => $data['title'],
                'start_date' => $data['start_date'] ?? null,
                'end_date'   => $data['end_date'] ?? null,
            ])->save();

            $existing = $trip->items()->withTrashed()->get()->keyBy('uuid');

            foreach ($kept as $row) {
                $item = $existing->get($row['id'])
                    ?? new TripItem(['uuid' => $row['id'], 'trip_id' => $trip->id]);

                $item->fill([
                    'place_id'   => $row['place_id'],
                    'day_number' => $row['day_number'],
                    'sort_order' => $row['sort_order'] ?? 0,
                    'note'       => $row['note'] ?? null,
                ]);
                $item->deleted_at = null;   // bring back an item that was removed earlier
                $item->save();
            }

            // items missing from the request were removed on the phone
            $trip->items()->whereNotIn('uuid', $kept->pluck('id')->all())->get()->each->delete();

            $trip->touch();
        });

        return (new TripResource($trip->load('items.place.media')))
            ->additional(['skipped' => $skipped->all()])
            ->response()
            ->setStatusCode($created ? 201 : 200);
    }

    /** DELETE is idempotent: an unknown or already deleted trip also returns 204. */
    public function destroy(Request $request, string $uuid)
    {
        $request->user()->trips()->where('uuid', $uuid)->first()?->delete();

        return response()->noContent();
    }
}