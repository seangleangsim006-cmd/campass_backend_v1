<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class PhraseCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $guarded = [];

    public function phrases(): HasMany { return $this->hasMany(Phrase::class); }
}