<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Support\JsonSearch;

class PlaceController extends Controller
{
    /**
     * GET /places?destination=siem-reap&category=temple&q=bayon&featured=1
     * GET /places?lat=13.41&lng=103.86&radius_km=10     (nearby, sorted by distance)
     */
    public function index(Request $request)
    {
        $request->validate([
            'destination' => ['nullable', 'string', 'max:150'],
            'category'    => ['nullable', 'string', 'max:120'],
            'q'           => ['nullable', 'string', 'max:100'],
            'featured'    => ['nullable', 'boolean'],
            'lat'         => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng'         => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius_km'   => ['nullable', 'numeric', 'min:0.1', 'max:500'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Place::query()
            ->select('places.*')   // must come first, so later select additions keep places.*
            ->visible()
            ->with(['category', 'destination:id,slug,name,is_premium', 'media'])
            ->withExists(['stories as has_story' => fn ($q) => $q->published()]);

        if ($slug = $request->query('destination')) {
            $query->whereHas('destination', fn ($q) => $q->where('slug', $slug));
        }

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        JsonSearch::in($query, 'name', $request->query('q'));

        if ($request->filled('lat')) {
            $lat    = (float) $request->query('lat');
            $lng    = (float) $request->query('lng');
            $radius = (float) $request->query('radius_km', 25);

            // Haversine formula (km). LEAST/GREATEST protect acos() from rounding errors.
            $distance = '(6371 * acos(LEAST(1, GREATEST(-1,
                cos(radians(?)) * cos(radians(places.latitude)) * cos(radians(places.longitude) - radians(?))
                + sin(radians(?)) * sin(radians(places.latitude))))))';

            $query->selectRaw("{$distance} as distance_km", [$lat, $lng, $lat])
                  ->whereRaw("{$distance} <= ?", [$lat, $lng, $lat, $radius])
                  ->orderBy('distance_km');
        } else {
            $query->orderByDesc('is_featured')->orderBy('id');
        }

        return PlaceResource::collection($query->paginate((int) $request->query('per_page', 20)));
    }

    public function show(Request $request, string $slug)
    {
        $place = Place::visible()
            ->where('slug', $slug)
            ->with([
                'category',
                'destination',
                'media',
                'tips',
                'stories' => fn ($q) => $q->published()->with('media'),
            ])
            ->firstOrFail();

        $user = $request->user('sanctum');

        return (new PlaceResource($place))->additional([
            'is_saved' => $user ? $user->savedPlaces()->whereKey($place->id)->exists() : false,
        ]);
    }
}