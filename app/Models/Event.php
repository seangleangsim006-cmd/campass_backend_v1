<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;

use App\Models\Concerns\Publishable;
use App\Observers\BumpDestinationVersion;

#[ObservedBy(BumpDestinationVersion::class)]
class Event extends Model implements HasMedia
{
    use HasTranslations, SoftDeletes, Publishable, InteractsWithMedia;

    public array $translatable = ['name', 'description'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_on'           => 'date',
            'ends_on'             => 'date',
            'is_recurring_yearly' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];

        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection('gallery')->acceptsMimeTypes($images);   // many images
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()->where(function (Builder $q) {
            $q->whereNull('events.destination_id')
              ->orWhereHas('destination', fn (Builder $d) => $d->published());
        });
    }

    /**
     * The current or next occurrence. A yearly event moves its stored example dates
     * forward by whole years until it has not ended yet.
     *
     * @return array{0: Carbon, 1: Carbon}  [start, end]
     */
    public function occurrence(?Carbon $today = null): array
    {
        $today ??= Carbon::today();

        $start = $this->starts_on->copy();
        $end   = ($this->ends_on ?? $this->starts_on)->copy();

        if ($this->is_recurring_yearly) {
            $years = max(0, $today->year - $end->year);

            $start->addYearsNoOverflow($years);
            $end->addYearsNoOverflow($years);

            if ($end->lt($today)) {
                $start->addYearNoOverflow();
                $end->addYearNoOverflow();
            }
        }

        return [$start, $end];
    }
}