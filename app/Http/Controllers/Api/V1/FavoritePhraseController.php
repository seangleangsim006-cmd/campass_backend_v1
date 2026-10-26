<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhraseResource;
use App\Models\Phrase;

class FavoritePhraseController extends Controller
{
    public function index(Request $request)
    {
        $phrases = Phrase::published()
            ->select('phrases.*')
            ->join('favorite_phrases', 'favorite_phrases.phrase_id', '=', 'phrases.id')
            ->where('favorite_phrases.user_id', $request->user()->id)
            ->with(['category', 'media'])
            ->orderByDesc('favorite_phrases.created_at')
            ->get();

        return PhraseResource::collection($phrases);
    }

    /** PUT is idempotent, so the app can retry it after being offline. */
    public function store(Request $request, int $phrase)
    {
        $phrase = Phrase::published()->findOrFail($phrase);

        DB::table('favorite_phrases')->insertOrIgnore([
            'user_id'    => $request->user()->id,
            'phrase_id'  => $phrase->id,
            'created_at' => now(),
        ]);

        return response()->json(['favorite' => true]);
    }

    public function destroy(Request $request, int $phrase)
    {
        $request->user()->favoritePhrases()->detach($phrase);

        return response()->json(['favorite' => false]);
    }
}