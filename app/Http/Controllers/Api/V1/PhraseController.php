<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhraseResource;
use App\Models\Phrase;
use App\Support\JsonSearch;

class PhraseController extends Controller
{
    /** GET /phrases?category=greetings&emergency=1&q=hello */
    public function index(Request $request)
    {
        $request->validate([
            'category'  => ['nullable', 'string', 'max:120'],
            'emergency' => ['nullable', 'boolean'],
            'q'         => ['nullable', 'string', 'max:100'],
        ]);

        $query = Phrase::published()->with(['category', 'media']);

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }

        if ($request->boolean('emergency')) {
            $query->where('is_emergency', true);
        }

        if (trim((string) $request->query('q')) !== '') {
            $like   = JsonSearch::pattern($request->query('q'));
            $locale = app()->getLocale();

            $query->where(function ($q) use ($like, $locale) {
                $q->where('khmer_text', 'ilike', $like)
                  ->orWhere('romanization', 'ilike', $like)
                  ->orWhere("translations->{$locale}", 'ilike', $like)
                  ->orWhere('translations->en', 'ilike', $like);
            });
        }

        return PhraseResource::collection(
            $query->orderBy('phrase_category_id')->orderBy('id')->get()
        );
    }
}