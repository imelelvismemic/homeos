<?php

namespace App\Providers\Filament;

use App\Platform\Filament\Pages\RegisterHousehold;
use App\Platform\Models\Household;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class HomePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('app')
            ->path('')
            ->login()
            ->registration()
            ->passwordReset()
            ->tenant(Household::class)
            ->tenantRegistration(RegisterHousehold::class)
            // Custom Filament tema (Tailwind v3 + token sistem iz CLAUDE.md tačke 6)
            // se aktivira u Fazi 2 preko ->viteTheme(...). Do tada panel koristi
            // Filament-ov ispravan pre-kompajlirani CSS. Build scaffold za temu
            // (resources/css/filament/app/*) već postoji radi Faza 0 tačke 9.
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Platform/Filament/Resources'), for: 'App\\Platform\\Filament\\Resources')
            ->discoverPages(in: app_path('Platform/Filament/Pages'), for: 'App\\Platform\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Platform/Filament/Widgets'), for: 'App\\Platform\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        // Svaki modul (app/Modules/<Ime>) se auto-discoveruje po konvenciji
        // foldera — CLAUDE.md §4/§5, nikad ručno registrovan u ovom provideru.
        foreach (glob(app_path('Modules/*'), GLOB_ONLYDIR) as $modulePath) {
            $moduleName = basename($modulePath);
            $moduleNamespace = "App\\Modules\\{$moduleName}\\Filament";

            if (is_dir("{$modulePath}/Filament/Resources")) {
                $panel->discoverResources(in: "{$modulePath}/Filament/Resources", for: "{$moduleNamespace}\\Resources");
            }

            if (is_dir("{$modulePath}/Filament/Pages")) {
                $panel->discoverPages(in: "{$modulePath}/Filament/Pages", for: "{$moduleNamespace}\\Pages");
            }

            if (is_dir("{$modulePath}/Filament/Widgets")) {
                $panel->discoverWidgets(in: "{$modulePath}/Filament/Widgets", for: "{$moduleNamespace}\\Widgets");
            }
        }

        return $panel;
    }
}
