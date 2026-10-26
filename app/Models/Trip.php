<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

use App\Models\Concerns\HasUuid;

class Trip extends Model
{
    use SoftDeletes, HasUuid;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function items(): HasMany
    {
        return $this->hasMany(TripItem::class)->orderBy('day_number')->orderBy('sort_order');
    }
}