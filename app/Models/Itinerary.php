<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;

use App\Observers\BumpDestinationVersion;
use App\Models\Concerns\Publishable;

#[ObservedBy(BumpDestinationVersion::class)]
class Itinerary extends Model implements HasMedia
{
    use HasTranslations, SoftDeletes, InteractsWithMedia, Publishable;

    public array $translatable = ['title', 'description'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_premium'      => 'boolean',
            'budget_estimate' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)->orderBy('day_number')->orderBy('sort_order');
    }

    public function scopeVisible(Builder $query, bool $includePremium = true): Builder
    {
        // published, and its destination is published too
        return $query->published()
            ->whereHas('destination', function (Builder $q) use ($includePremium) {
                $q->published();

                if (! $includePremium) {
                    $q->where('is_premium', false);
                }
            })
            ->when(! $includePremium, fn(Builder $q) => $q->where('itineraries.is_premium', false));
    }

    /** Eager loads what the API needs: only stops whose place is visible. */
    public function scopeForApi(Builder $query): Builder
    {
        return $query
            ->with([
                'destination:id,slug,name,is_premium',
                'media',
                'items' => fn($q) => $q->whereHas('place', fn($p) => $p->visible())->with('place.media'),
            ])
            ->withCount(['items' => fn($q) => $q->whereHas('place', fn($p) => $p->visible())]);
    }
}
