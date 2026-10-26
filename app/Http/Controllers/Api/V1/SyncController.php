<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Resources\{DestinationResource, EmergencyContactResource, EventResource, ItineraryResource, PhraseResource, PlaceResource, PlaceStoryResource, PlaceTipResource, TransportRouteResource};
use App\Models\{Destination, EmergencyContact, Event, Itinerary, Phrase, Place, PlaceStory, PlaceTip, TransportRoute};
use App\Support\Premium;

class SyncController extends Controller
{
    /**
     * GET /destinations/{slug}/pack
     * First download of a destination for offline use. All languages included.
     */
    public function pack(Request $request, string $slug)
    {
        $request->merge(['all_languages' => true]);

        $premium = Premium::allows($request->user('sanctum'));

        $destination = Destination::published()
            ->where('slug', $slug)
            ->with(['region', 'media'])
            ->firstOrFail();

        if ($destination->is_premium && ! $premium) {
            return response()->json([
                'message' => 'This guide is premium content.',
                'code'    => 'premium_required',
            ], 403);
        }

        $places = $destination->places()->published()
            ->with([
                'category', 'media', 'tips',
                'stories' => fn ($q) => $q->published()->with('media'),
            ])
            ->get()
            ->each(fn ($place) => $place->setRelation('destination', $destination));

        $contacts = EmergencyContact::with('destination:id,slug')
            ->where(fn ($q) => $q->whereNull('destination_id')->orWhere('destination_id', $destination->id))
            ->get();

        $itineraries = Itinerary::visible($premium)
            ->forApi()
            ->where('itineraries.destination_id', $destination->id)
            ->get();

        $routes = TransportRoute::visible()
            ->where(fn ($q) => $q
                ->where('transport_routes.from_destination_id', $destination->id)
                ->orWhere('transport_routes.to_destination_id', $destination->id))
            ->with(['fromDestination:id,slug,name', 'toDestination:id,slug,name'])
            ->get();

        $events = Event::visible()
            ->where(fn ($q) => $q->whereNull('events.destination_id')->orWhere('events.destination_id', $destination->id))
            ->with('destination:id,slug,name')
            ->get();

        return response()->json([
            'version'            => $destination->version,
            'generated_at'       => now()->utc()->toIso8601ZuluString(),   // use as the first "since"
            'destination'        => DestinationResource::make($destination)->resolve($request),
            'places'             => PlaceResource::collection($places)->resolve($request),
            'phrases'            => PhraseResource::collection(Phrase::published()->with('media')->get())->resolve($request),
            'emergency_contacts' => EmergencyContactResource::collection($contacts)->resolve($request),
            'itineraries'        => ItineraryResource::collection($itineraries)->resolve($request),
            'transport_routes'   => TransportRouteResource::collection($routes)->resolve($request),
            'events'             => EventResource::collection($events)->resolve($request),
        ]);
    }

    /**
     * GET /sync?since=2026-10-18T03:00:00Z
     * Everything that changed since the given time, plus the ids that must be deleted.
     */
    public function index(Request $request)
    {
        $request->validate(['since' => ['required', 'date']]);
        $request->merge(['all_languages' => true]);

        // taken BEFORE querying, so a change made during the queries is never missed
        $serverTime = now();

        // database timestamps are stored in the app timezone
        $since   = Carbon::parse($request->query('since'))->setTimezone(config('app.timezone'));
        $premium = Premium::allows($request->user('sanctum'));

        $destinations = Destination::published()
            ->where('updated_at', '>=', $since)
            ->when(! $premium, fn ($q) => $q->where('is_premium', false))
            ->with(['region', 'media'])
            ->get();

        $places = Place::query()->visible($premium)
            ->where('places.updated_at', '>=', $since)
            ->with(['category', 'destination:id,slug,name,is_premium', 'media'])
            ->get();

        $stories = PlaceStory::published()
            ->where('place_stories.updated_at', '>=', $since)
            ->whereHas('place', fn ($q) => $q->visible($premium))
            ->with('media')
            ->get();

        $tips = PlaceTip::where('place_tips.updated_at', '>=', $since)
            ->whereHas('place', fn ($q) => $q->visible($premium))
            ->get();

        $phrases  = Phrase::published()->where('phrases.updated_at', '>=', $since)->with('media')->get();
        $contacts = EmergencyContact::where('updated_at', '>=', $since)->with('destination:id,slug')->get();

        // an itinerary is re-sent with ALL its stops whenever one stop changes
        $itineraries = Itinerary::visible($premium)
            ->forApi()
            ->where('itineraries.updated_at', '>=', $since)
            ->get();

        $routes = TransportRoute::visible()
            ->where('transport_routes.updated_at', '>=', $since)
            ->with(['fromDestination:id,slug,name', 'toDestination:id,slug,name'])
            ->get();

        $events = Event::visible()
            ->where('events.updated_at', '>=', $since)
            ->with('destination:id,slug,name')
            ->get();

        return response()->json([
            'server_time' => $serverTime->copy()->utc()->toIso8601ZuluString(),   // store it, send it as the next "since"
            'updated' => [
                'destinations'       => DestinationResource::collection($destinations)->resolve($request),
                'places'             => PlaceResource::collection($places)->resolve($request),
                'stories'            => PlaceStoryResource::collection($stories)->resolve($request),
                'tips'               => PlaceTipResource::collection($tips)->resolve($request),
                'phrases'            => PhraseResource::collection($phrases)->resolve($request),
                'emergency_contacts' => EmergencyContactResource::collection($contacts)->resolve($request),
                'itineraries'        => ItineraryResource::collection($itineraries)->resolve($request),
                'transport_routes'   => TransportRouteResource::collection($routes)->resolve($request),
                'events'             => EventResource::collection($events)->resolve($request),
            ],
            'removed' => [
                'destinations'       => $this->removed(Destination::class, $since),
                'places'             => $this->removed(Place::class, $since),
                'stories'            => $this->removed(PlaceStory::class, $since),
                'tips'               => $this->removed(PlaceTip::class, $since, hasStatus: false),
                'phrases'            => $this->removed(Phrase::class, $since),
                'emergency_contacts' => $this->removed(EmergencyContact::class, $since, hasStatus: false),
                'itineraries'        => $this->removed(Itinerary::class, $since),
                'transport_routes'   => $this->removed(TransportRoute::class, $since),
                'events'             => $this->removed(Event::class, $since),
            ],
        ]);
    }

    /** Ids that were soft-deleted or un-published since the given time. */
    private function removed(string $model, Carbon $since, bool $hasStatus = true): array
    {
        return $model::withTrashed()
            ->where('updated_at', '>=', $since)
            ->where(function ($q) use ($hasStatus) {
                $q->whereNotNull('deleted_at');

                if ($hasStatus) {
                    $q->orWhere('status', '!=', 'published');
                }
            })
            ->pluck('id')
            ->all();
    }
}