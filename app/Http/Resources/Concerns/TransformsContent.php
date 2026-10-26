<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait TransformsContent
{
    /**
     * Translatable field: current language by default,
     * every language when the request has ?all_languages=1 (offline packs and sync).
     */
    protected function tr(Request $request, string $field, ?Model $model = null): mixed
    {
        $model ??= $this->resource;

        $value = $request->boolean('all_languages')
            ? $model->getTranslations($field)
            : $model->getTranslation($field, app()->getLocale());   // falls back to English

        return $value ?: null;
    }

    protected function num(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    protected function mediaUrl(string $collection): ?string
    {
        return $this->resource->getFirstMediaUrl($collection) ?: null;
    }

    protected function galleryUrls(): array
    {
        return $this->resource->getMedia('gallery')->map(fn ($m) => $m->getUrl())->all();
    }
}