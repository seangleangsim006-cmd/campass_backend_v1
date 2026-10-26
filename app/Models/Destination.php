<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia};
use Spatie\Translatable\HasTranslations;

use App\Models\Concerns\Publishable;

class Destination extends Model implements HasMedia
{
    use HasTranslations, SoftDeletes, InteractsWithMedia, Publishable;

    public array $translatable = ['name', 'tagline', 'description', 'best_season', 'how_to_get_there'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_premium'   => 'boolean',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function region(): BelongsTo          { return $this->belongsTo(Region::class); }
    public function places(): HasMany            { return $this->hasMany(Place::class); }
    public function itineraries(): HasMany       { return $this->hasMany(Itinerary::class); }
    public function emergencyContacts(): HasMany { return $this->hasMany(EmergencyContact::class); }
    public function events(): HasMany            { return $this->hasMany(Event::class); }
    public function routesFrom(): HasMany        { return $this->hasMany(TransportRoute::class, 'from_destination_id'); }
    public function routesTo(): HasMany          { return $this->hasMany(TransportRoute::class, 'to_destination_id'); }
}