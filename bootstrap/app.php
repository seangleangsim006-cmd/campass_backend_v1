<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Request;

use App\Http\Middleware\SetApiLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    // inside Application::configure(...)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [SetApiLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API errors are always JSON, even if the app forgets the Accept header
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
