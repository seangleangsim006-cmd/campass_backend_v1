<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Region extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $guarded = [];

    public function destinations(): HasMany { return $this->hasMany(Destination::class); }
}