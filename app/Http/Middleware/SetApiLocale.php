<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class SetApiLocale
{
    public const SUPPORTED = ['en', 'km', 'zh', 'ko'];

    public function handle(Request $request, Closure $next)
    {
        // ?locale=km wins, otherwise the Accept-Language header ("km-KH,km;q=0.9,en;q=0.8")
        $raw = $request->query('locale') ?? $request->header('Accept-Language');

        $locale = collect(explode(',', (string) $raw))
            ->map(fn ($part) => strtolower(substr(trim(explode(';', $part)[0]), 0, 2)))
            ->first(fn ($code) => in_array($code, self::SUPPORTED, true));

        app()->setLocale($locale ?? 'en');

        return $next($request);
    }
}