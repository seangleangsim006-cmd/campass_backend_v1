<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    /** GET /events?destination=siem-reap&include_past=1 */
    public function index(Request $request)
    {
        $request->validate([
            'destination'  => ['nullable', 'string', 'max:150'],
            'include_past' => ['nullable', 'boolean'],
        ]);

        $today = Carbon::today();
        $slug  = $request->query('destination');

        $events = Event::visible()
            ->with('destination:id,slug,name')
            ->when($slug, fn ($q) => $q->where(function ($w) use ($slug) {
                $w->whereNull('events.destination_id')
                  ->orWhereHas('destination', fn ($d) => $d->where('slug', $slug));
            }))
            ->get();

        // few events, so dates are worked out in PHP (yearly events move forward)
        if (! $request->boolean('include_past')) {
            $events = $events->filter(fn (Event $e) => $e->occurrence($today)[1]->gte($today));
        }

        return EventResource::collection(
            $events->sortBy(fn (Event $e) => $e->occurrence($today)[0]->timestamp)->values()
        );
    }
}