<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\DestinationResource;
use App\Models\Destination;
use App\Support\JsonSearch;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'region' => ['nullable', 'string', 'max:120'],
            'q'      => ['nullable', 'string', 'max:100'],
        ]);

        $query = Destination::published()
            ->with(['region', 'media'])
            ->withCount(['places' => fn ($q) => $q->published()]);

        if ($region = $request->query('region')) {
            $query->whereHas('region', fn ($q) => $q->where('slug', $region));
        }

        JsonSearch::in($query, 'name', $request->query('q'));

        return DestinationResource::collection($query->orderBy('id')->get());
    }

    public function show(string $slug)
    {
        $destination = Destination::published()
            ->where('slug', $slug)
            ->with([
                'region',
                'media',
                'emergencyContacts',
                'places' => fn ($q) => $q->published()
                    ->with(['category', 'media'])
                    ->orderByDesc('is_featured')
                    ->orderBy('id'),
            ])
            ->firstOrFail();

        return new DestinationResource($destination);
    }
}