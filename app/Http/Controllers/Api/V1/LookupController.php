<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\LookupResource;
use App\Models\{PhraseCategory, PlaceCategory, Region};

class LookupController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => [
            'regions'           => LookupResource::collection(Region::orderBy('sort_order')->get())->resolve($request),
            'place_categories'  => LookupResource::collection(PlaceCategory::orderBy('sort_order')->get())->resolve($request),
            'phrase_categories' => LookupResource::collection(PhraseCategory::orderBy('sort_order')->get())->resolve($request),
        ]]);
    }
}