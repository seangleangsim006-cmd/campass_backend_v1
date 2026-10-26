<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ItineraryItem extends Model
{
    use HasTranslations;

    public array $translatable = ['note'];

    protected $guarded = [];
    protected $touches = ['itinerary'];
    public function itinerary(): BelongsTo { return $this->belongsTo(Itinerary::class); }
    public function place(): BelongsTo     { return $this->belongsTo(Place::class); }
}