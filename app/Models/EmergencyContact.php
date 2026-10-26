<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class EmergencyContact extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'address'];

    protected $guarded = [];

    // destination_id = null means nationwide
    public function destination(): BelongsTo { return $this->belongsTo(Destination::class); }
}