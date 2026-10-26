<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};

use App\Models\Concerns\Publishable;

class Phrase extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Publishable;

    // "translations" is a plain JSON column: {"en": "...", "zh": "...", "ko": "..."}
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'is_emergency' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')->singleFile();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PhraseCategory::class, 'phrase_category_id');
    }
}