<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            // keep the uuid if the mobile app already created one offline
            $model->uuid ??= (string) Str::uuid();
        });
    }
}