<?php

namespace App\Providers\Filament;

use App\Platform\Filament\Pages\Dashboard;
use App\Platform\Filament\Pages\NotificationsInbox;
use App\Platform\Filament\Pages\RegisterHousehold;
use App\Platform\Filament\Pages\UserProfile;
use App\Platform\Filament\Tenancy\EditHouseholdProfile;
use App\Platform\Http\AvatarController;
use App\Platform\Http\CalendarEventsController;
use App\Platform\Http\QuickCreateController;
use App\Platform\Http\SearchController;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
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
            // Profil NIJE Filamentov ->profile(): ta stranica se registruje izvan
            // tenant rute, pa panel layout puca (500) jer navigacija traži tenant.
            // Naša UserProfile je obična stranica panela, dostupna iz korisničkog menija.
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => __('platform.profile.title'))
                    ->icon('heroicon-m-user-circle')
                    // Korisnički meni se renderuje i na stranicama BEZ domaćinstva
                    // (npr. kreiranje prvog domaćinstva), a Filament računa URL prije
                    // provjere vidljivosti — bez ovog guarda getUrl() puca na
                    // nedostajućem {tenant} parametru i cijela stranica vrati 500.
                    ->visible(fn (): bool => Filament::getTenant() !== null)
                    ->url(fn (): ?string => Filament::getTenant() ? UserProfile::getUrl() : null),
            ])
            ->tenant(Household::class)
            // Kreiranje domaćinstva se NE nudi kao stalna opcija u meniju — stranica
            // je vidljiva samo korisniku koji još nema nijedno domaćinstvo
            // (RegisterHousehold::canView). Filament tada sam preusmjeri na nju
            // nakon prijave, čime je pokriven slučaj "registrovao se pa zatvorio
            // browser prije nego je dovršio kreiranje".
            ->tenantRegistration(RegisterHousehold::class)
            // Izmjena naziva domaćinstva (samo vlasnik) — stranica je unutar
            // tenant ruta, pa ima puni kontekst domaćinstva.
            ->tenantProfile(EditHouseholdProfile::class)
            // Endpointi koje topbar modali (pretraga, brzo dodavanje) zovu fetch-om.
            // Rute panela → SetUpPanel middleware (Filament kontekst); auth/tenant
            // provjera je u kontrolerima (kao SearchController).
            ->routes(function (): void {
                Route::get('/pretraga', SearchController::class)->name('search');
                Route::post('/brzo/{key}', QuickCreateController::class)->name('quick-create');
                // Kalendar dohvata događaje po prikazanom rasponu (FullCalendar feed),
                // pa se nakon brzog dodavanja osvježi bez promjene mjeseca.
                Route::get('/kalendar/dogadjaji', CalendarEventsController::class)->name('calendar-events');
                // Profilna slika s privatnog diska (autentikovano).
                Route::get('/profil/slika/{user}', AvatarController::class)->name('avatar');
            })
            // Custom tema "Topli dom" (CLAUDE.md §6). Paleta kroz ->colors()
            // (Filament generiše CSS varijable); Fraunces/Inter i signature
            // stilovi u resources/css/filament/app/theme.css (Tailwind v3).
            ->viteTheme('resources/css/filament/app/theme.css')
            ->font('Inter')
            ->colors([
                'primary' => Color::hex('#BF6A44'), // terakota
                'gray' => Color::Stone,             // topli neutralni tonovi (krem)
                'success' => Color::hex('#4E8D5B'),
                'warning' => Color::hex('#D99A3C'),
                'danger' => Color::hex('#B23B2E'),
                'info' => Color::hex('#3E7C8C'),
            ])
            // Redoslijed grupa u meniju je fiksiran ovdje; bez ovoga ih Filament
            // slaže onim redom kojim ih zatekne kroz auto-discovery modula.
            // Nazivi moraju biti isti stringovi koje moduli vraćaju iz
            // getNavigationGroup() (lang/bs/<modul>.php → navigation_group).
            ->navigationGroups([
                __('tasks.navigation_group'),
                __('finance.navigation_group'),
                __('lifeadmin.navigation_group'),
            ])
            ->discoverResources(in: app_path('Platform/Filament/Resources'), for: 'App\\Platform\\Filament\\Resources')
            ->discoverPages(in: app_path('Platform/Filament/Pages'), for: 'App\\Platform\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Platform/Filament/Widgets'), for: 'App\\Platform\\Filament\\Widgets')
            // Dashboard widgete kontroliše naš Dashboard (registry iz
            // config/homeos-apps.php), ne default Filament promo widgeti.
            ->widgets([])
            // Univerzalna pretraga (command palette, Ctrl/Cmd+K) na početku topbara,
            // ispred hamburgera na tabletu/mobilnom. Čisti Alpine modal + fetch ka
            // /pretraga (bez Livewire → nema /livewire/update ni 419).
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                function (): string {
                    $tenant = Filament::getTenant();

                    if (! $tenant) {
                        return '';
                    }

                    return view('filament.platform.command-palette', [
                        'searchUrl' => route('filament.app.search', ['h' => $tenant->getKey()]),
                    ])->render();
                },
            )
            // "Brzo dodaj" — Alpine modal nad trenutnom stranicom (zamagljena
            // pozadina, kao command palette): korisnik doda minimalne podatke,
            // snimi šalje fetch POST na /brzo/{key}, modal se zatvori i korisnik
            // ostaje gdje je bio (bez navigacije, bez Livewire → bez 419).
            // GLOBAL_SEARCH_AFTER (a ne TOPBAR_END) jer se TOPBAR_END renderuje
            // NAKON korisničkog menija — a redoslijed s desna nalijevo treba biti:
            // profil, zvonce, "Brzo dodaj".
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                function (): string {
                    $tenant = Filament::getTenant();

                    if (! $tenant) {
                        return '';
                    }

                    return view('filament.platform.quick-capture', [
                        'items' => app(QuickCaptureRegistry::class)->items(),
                        // Placeholder ključ mijenja Alpine po tipu; h je tenant.
                        'postUrlTemplate' => route('filament.app.quick-create', ['key' => '__KEY__', 'h' => $tenant->getKey()]),
                        'csrfToken' => csrf_token(),
                    ])->render();
                },
            )
            // Zvonce obavještenja — link na sanduče trenutnog člana + brojač nepročitanih.
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                function (): string {
                    $tenant = Filament::getTenant();

                    if (! $tenant) {
                        return '';
                    }

                    $member = $tenant->members()->where('user_id', auth()->id())->first();

                    return view('filament.platform.notification-bell', [
                        'inboxUrl' => NotificationsInbox::getUrl(tenant: $tenant),
                        'unreadCount' => $member?->unreadNotifications()->count() ?? 0,
                    ])->render();
                },
            )
            // Na mobilnom: zatvori bočni meni kad korisnik klikne bilo koji link
            // (Filament to radi samo za stavke menija, pa je npr. "Postavke
            // domaćinstva" iz padajućeg menija ostavljalo meni otvorenim).
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => view('filament.platform.sidebar-autoclose')->render(),
            )
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
