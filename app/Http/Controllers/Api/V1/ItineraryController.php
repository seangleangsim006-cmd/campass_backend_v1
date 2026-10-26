<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItineraryResource;
use App\Models\Itinerary;
use App\Support\JsonSearch;

class ItineraryController extends Controller
{
    /** GET /itineraries?destination=siem-reap&q=temples */
    public function index(Request $request)
    {
        $request->validate([
            'destination' => ['nullable', 'string', 'max:150'],
            'q'           => ['nullable', 'string', 'max:100'],
        ]);

        $query = Itinerary::visible()->forApi();

        if ($slug = $request->query('destination')) {
            $query->whereHas('destination', fn ($q) => $q->where('slug', $slug));
        }

        JsonSearch::in($query, 'title', $request->query('q'));

        // the list is a teaser: counts only, no stops
        return ItineraryResource::collection(
            $query->orderBy('id')->get()->each(fn ($itinerary) => $itinerary->unsetRelation('items'))
        );
    }

    public function show(string $slug)
    {
        $itinerary = Itinerary::visible()
            ->forApi()
            ->where('slug', $slug)
            ->firstOrFail();

        return new ItineraryResource($itinerary);
    }
}