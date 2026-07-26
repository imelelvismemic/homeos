<?php

namespace App\Providers;

use App\Platform\Backup\DatabaseDumper;
use App\Platform\Backup\MysqlDumper;
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

        // Backup (Faza 8): interfejs postoji da bi se backup mogao testirati bez
        // stvarnog `mysqldump` procesa — pa mu OVDJE mora stajati produkcijska
        // implementacija. Bez ovog veza, komanda puca tek na serveru ("Target
        // [DatabaseDumper] is not instantiable"), jer testovi vežu svoju lažnu.
        $this->app->bind(DatabaseDumper::class, MysqlDumper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
