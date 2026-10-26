<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmergencyContactResource;
use App\Models\EmergencyContact;

class EmergencyContactController extends Controller
{
    /** GET /emergency-contacts?destination=siem-reap  → nationwide contacts + that destination's */
    public function index(Request $request)
    {
        $request->validate(['destination' => ['nullable', 'string', 'max:150']]);

        $query = EmergencyContact::query()->with('destination:id,slug');

        if ($slug = $request->query('destination')) {
            $query->where(function ($q) use ($slug) {
                $q->whereNull('destination_id')
                  ->orWhereHas('destination', fn ($d) => $d->where('slug', $slug));
            });
        }

        return EmergencyContactResource::collection($query->orderBy('id')->get());
    }
}