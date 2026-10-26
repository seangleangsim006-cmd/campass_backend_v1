<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;

use App\Models\Concerns\Publishable;
use App\Observers\BumpDestinationVersion;

#[ObservedBy(BumpDestinationVersion::class)]
class Place extends Model implements HasMedia
{
    use HasTranslations, SoftDeletes, InteractsWithMedia, Publishable;

    public array $translatable = [
        'name',
        'description',
        'fee_note',
        'best_time',
        'dress_code',
        'accessibility',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'is_featured'   => 'boolean',
            'latitude'      => 'decimal:7',
            'longitude'     => 'decimal:7',
            'entrance_fee'  => 'decimal:2',
            'published_at'  => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(PlaceCategory::class, 'place_category_id');
    }
    public function stories(): HasMany
    {
        return $this->hasMany(PlaceStory::class)->orderBy('sort_order');
    }
    public function tips(): HasMany
    {
        return $this->hasMany(PlaceTip::class)->orderBy('sort_order');
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_places');
    }


    public function scopeVisible(Builder $query, bool $includePremium = true): Builder
    {
        // a place is visible only if it AND its destination are published
        return $query->published()->whereHas('destination', function (Builder $q) use ($includePremium) {
            $q->published();

            if (! $includePremium) {
                $q->where('is_premium', false);
            }
        });
    }
}
