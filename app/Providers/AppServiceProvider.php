<?php

namespace App\Providers;

use App\Console\Commands\FixLegacyAraguaSeedMunicipiosEstadoCommand;
use App\Console\Commands\ImportGeografiaDumpsCommand;
use App\Models\Factura;
use App\Policies\FacturaPolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            ImportGeografiaDumpsCommand::class,
            FixLegacyAraguaSeedMunicipiosEstadoCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Factura::class, FacturaPolicy::class);

        // Fechas legibles en español (meses en UI, p. ej. dashboard) sin tocar rutas ni lógica.
        Carbon::setLocale(config('app.locale'));

        // Si APP_URL es https (demo por túnel), forzar https en asset()/@vite para evitar Mixed Content
        // (página en HTTPS pero CSS/JS pedidos por HTTP y bloqueados por el navegador).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
