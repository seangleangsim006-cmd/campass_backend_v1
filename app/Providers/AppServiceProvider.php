<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Media::created(fn(Media $media) => $media->model?->touch());
        Media::deleted(fn(Media $media) => $media->model?->touch());
        Media::updated(function (Media $media) {
            if ($media->wasChanged('order_column')) {
                $media->model?->touch();
            }
        });
    }
}
