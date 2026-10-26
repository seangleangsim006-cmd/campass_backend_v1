<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

use App\Observers\BumpDestinationVersion;

#[ObservedBy(BumpDestinationVersion::class)]
class PlaceTip extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['text'];

    protected $guarded = [];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
