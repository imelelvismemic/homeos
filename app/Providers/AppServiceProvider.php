<?php

namespace App\Providers;

use App\Platform\Modules\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registry modula drži keš odstupanja po domaćinstvu unutar zahtjeva —
        // ima smisla samo kao singleton (ROADMAP Faza 7).
        $this->app->singleton(ModuleRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
