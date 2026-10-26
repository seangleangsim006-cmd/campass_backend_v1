<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class PlaceCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $guarded = [];

    public function places(): HasMany { return $this->hasMany(Place::class); }
}