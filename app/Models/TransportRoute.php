<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\Translatable\HasTranslations;

use App\Observers\BumpDestinationVersion;
use App\Models\Concerns\Publishable;

#[ObservedBy(BumpDestinationVersion::class)]
class TransportRoute extends Model
{
    use HasTranslations, SoftDeletes, Publishable;

    public array $translatable = ['notes'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
        ];
    }

    public function fromDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'from_destination_id');
    }

    public function toDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'to_destination_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()
            ->whereHas('fromDestination', fn(Builder $q) => $q->published())
            ->whereHas('toDestination', fn(Builder $q) => $q->published());
    }
}
