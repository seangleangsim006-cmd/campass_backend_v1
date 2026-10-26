<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Concerns\HasUuid;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes, HasUuid;

    protected $fillable = [
        'name', 'email', 'password', 'avatar',
        'country_code', 'preferred_language', 'last_active_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function socialAccounts(): HasMany { return $this->hasMany(SocialAccount::class); }
    public function trips(): HasMany          { return $this->hasMany(Trip::class); }
    public function deviceTokens(): HasMany   { return $this->hasMany(DeviceToken::class); }
    public function downloads(): HasMany      { return $this->hasMany(Download::class); }

    public function savedPlaces(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'saved_places')->withPivot('created_at');
    }

    public function visitedPlaces(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'visited_places')->withPivot('visited_at');
    }

    public function favoritePhrases(): BelongsToMany
    {
        return $this->belongsToMany(Phrase::class, 'favorite_phrases')->withPivot('created_at');
    }
}