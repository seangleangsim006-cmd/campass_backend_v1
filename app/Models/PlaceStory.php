<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;

use App\Models\Concerns\Publishable;
use App\Observers\BumpDestinationVersion;

#[ObservedBy(BumpDestinationVersion::class)]
class PlaceStory extends Model implements HasMedia
{
    use HasTranslations, SoftDeletes, InteractsWithMedia, Publishable;

    public array $translatable = ['title', 'transcript'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['autoplay_on_arrival' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        // one audio file per language
        foreach (['en', 'km', 'zh', 'ko'] as $lang) {
            $this->addMediaCollection("audio_{$lang}")->singleFile();
        }
    }

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
}