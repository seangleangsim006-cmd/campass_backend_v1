<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportRouteResource;
use App\Models\TransportRoute;

class TransportRouteController extends Controller
{
    /** GET /transport-routes?from=siem-reap&to=phnom-penh&mode=bus&both_ways=1 */
    public function index(Request $request)
    {
        $request->validate([
            'from'      => ['nullable', 'string', 'max:150'],
            'to'        => ['nullable', 'string', 'max:150'],
            'mode'      => ['nullable', Rule::in(['bus', 'minivan', 'ferry', 'taxi', 'flight', 'train'])],
            'both_ways' => ['nullable', 'boolean'],
        ]);

        $from = $request->query('from');
        $to   = $request->query('to');

        $query = TransportRoute::visible()
            ->with(['fromDestination:id,slug,name', 'toDestination:id,slug,name']);

        if ($from && $to && $request->boolean('both_ways')) {
            $query->where(function ($outer) use ($from, $to) {
                $outer->where(fn ($q) => $q
                        ->whereHas('fromDestination', fn ($d) => $d->where('slug', $from))
                        ->whereHas('toDestination', fn ($d) => $d->where('slug', $to)))
                    ->orWhere(fn ($q) => $q
                        ->whereHas('fromDestination', fn ($d) => $d->where('slug', $to))
                        ->whereHas('toDestination', fn ($d) => $d->where('slug', $from)));
            });
        } else {
            if ($from) {
                $query->whereHas('fromDestination', fn ($d) => $d->where('slug', $from));
            }
            if ($to) {
                $query->whereHas('toDestination', fn ($d) => $d->where('slug', $to));
            }
        }

        if ($mode = $request->query('mode')) {
            $query->where('mode', $mode);
        }

        return TransportRouteResource::collection(
            $query->orderBy('duration_min')->orderBy('id')->get()
        );
    }
}