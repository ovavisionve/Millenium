<?php

use App\Http\Middleware\ConfigureUrlAndSessionForProxiedRequests;
use App\Http\Middleware\EnsureOperationalStaff;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Túneles (Cloudflare quick tunnel, ngrok, etc.): el navegador usa HTTPS pero PHP solo
        // ve HTTP en 127.0.0.1. Confiar en el proxy hace que Laravel lea X-Forwarded-Proto y genere URLs correctas.
        $middleware->trustProxies(at: '*');

        // Grupo web: cookies + sesión + CSRF (necesario para auth, profile y formularios).
        // Además forzamos URL/cookie correctas detrás de túneles/proxies.
        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            ConfigureUrlAndSessionForProxiedRequests::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'operational' => EnsureOperationalStaff::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
