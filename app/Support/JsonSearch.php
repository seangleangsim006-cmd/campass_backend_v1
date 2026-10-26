<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class JsonSearch
{
    /** LIKE pattern with user wildcards escaped. */
    public static function pattern(string $term): string
    {
        return '%' . str_replace(['%', '_'], ['\%', '\_'], trim($term)) . '%';
    }

    /** Search a translatable JSON column in the current language and in English. */
    public static function in(Builder $query, string $column, ?string $term): Builder
    {
        if (trim((string) $term) === '') {
            return $query;
        }

        $like    = self::pattern($term);
        $locales = array_unique([app()->getLocale(), 'en']);   // locale is whitelisted by the middleware

        return $query->where(function ($q) use ($column, $like, $locales) {
            foreach ($locales as $locale) {
                $q->orWhere("{$column}->{$locale}", 'ilike', $like);
            }
        });
    }
}