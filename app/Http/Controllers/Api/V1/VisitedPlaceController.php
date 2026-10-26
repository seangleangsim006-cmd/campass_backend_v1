<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;

class VisitedPlaceController extends Controller
{
    public function index(Request $request)
    {
        $places = Place::query()
            ->select('places.*')
            ->visible()
            ->join('visited_places', 'visited_places.place_id', '=', 'places.id')
            ->where('visited_places.user_id', $request->user()->id)
            ->addSelect('visited_places.visited_at as visited_at')
            ->with(['category', 'destination:id,slug,name,is_premium', 'media'])
            ->withExists(['stories as has_story' => fn ($q) => $q->published()])
            ->orderByDesc('visited_places.visited_at')
            ->get();

        return PlaceResource::collection($places);
    }

    /** The first visit date is kept; a retry never overwrites it. */
    public function store(Request $request, string $slug)
    {
        $request->validate(['visited_at' => ['nullable', 'date', 'before_or_equal:tomorrow']]);

        $place = Place::visible()->where('slug', $slug)->firstOrFail();

        $visitedAt = $request->filled('visited_at')
            ? Carbon::parse($request->input('visited_at'))->setTimezone(config('app.timezone'))
            : now();

        DB::table('visited_places')->insertOrIgnore([
            'user_id'    => $request->user()->id,
            'place_id'   => $place->id,
            'visited_at' => $visitedAt,
        ]);

        return response()->json(['visited' => true]);
    }

    public function destroy(Request $request, string $slug)
    {
        $place = Place::where('slug', $slug)->firstOrFail();

        $request->user()->visitedPlaces()->detach($place->id);

        return response()->json(['visited' => false]);
    }
}