<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;

class SavedPlaceController extends Controller
{
    public function index(Request $request)
    {
        $places = Place::query()
            ->select('places.*')
            ->visible()
            ->join('saved_places', 'saved_places.place_id', '=', 'places.id')
            ->where('saved_places.user_id', $request->user()->id)
            ->addSelect('saved_places.created_at as saved_at')
            ->with(['category', 'destination:id,slug,name,is_premium', 'media'])
            ->withExists(['stories as has_story' => fn ($q) => $q->published()])
            ->orderByDesc('saved_places.created_at')
            ->get();

        return PlaceResource::collection($places);
    }

    /** PUT is idempotent, so the app can safely retry it after being offline. */
    public function store(Request $request, string $slug)
    {
        $place = Place::visible()->where('slug', $slug)->firstOrFail();

        $request->user()->savedPlaces()
            ->syncWithoutDetaching([$place->id => ['created_at' => now()]]);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, string $slug)
    {
        $place = Place::where('slug', $slug)->firstOrFail();

        $request->user()->savedPlaces()->detach($place->id);

        return response()->json(['saved' => false]);
    }
}