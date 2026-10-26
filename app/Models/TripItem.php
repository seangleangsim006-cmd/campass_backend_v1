<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\HasUuid;

class TripItem extends Model
{
    use SoftDeletes, HasUuid;

    protected $guarded = [];

    public function trip(): BelongsTo  { return $this->belongsTo(Trip::class); }
    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
}