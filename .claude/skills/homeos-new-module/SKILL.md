---
name: homeos-new-module
description: Scaffold a new Home OS module (app) that plugs into the platform — Filament Resource, events, policy, sharing, dashboard widget, search, calendar source, notifications, scheduler, tests, and App Registry entry. Use when adding a new "app" like Reminders, Notes, Finance, or LifeAdmin.
---

# Home OS — dodavanje novog modula ("app")

Ovaj skill vodi kroz kreiranje novog modula tako da se uklopi u platformu bez
diranja core-a. Prati CLAUDE.md (posebno §4, §7–§14) i DATA_MODEL.md. Referentni
uzor koji već postoji u repou je **Tasks** modul (`app/Modules/Tasks`) — kad god
je nešto nejasno, pogledaj kako je tamo urađeno i preslikaj obrazac.

**Zlatno pravilo:** modul nikad ne importuje klase drugog modula. Komunikacija
ide isključivo preko `app/Platform` (eventi, contracts, sharing, notifikacije) i
preko `config/homeos-apps.php`. Core (dashboard, search, kalendar, navigacija,
brzo dodavanje) čita samo iz registryja — nikad hardkodovana lista modula.

## Ulazni podaci koje treba prikupiti prije scaffolda

Pitaj korisnika (ili izvedi iz zahtjeva) prije generisanja:

1. **Ime modula** u PascalCase (npr. `Reminders`, `Notes`, `Finance`).
2. **Glavni entitet** i njegova polja (uskladi nazive s DATA_MODEL.md §3 — npr.
   uvijek `due_date`, nikad `deadline_at`). Prefiks tabele je `snake_case` ime
   modula (npr. `reminders_`).
3. Da li entitet može biti **privatan/dijeljen** (skoro uvijek da → Shareable).
4. Da li ima **oznake** (Taggable) — dijeli platform tagove, ne pravi svoje.
5. Da li šalje **obavještenja** — koja kategorija (dopiši je u DATA_MODEL.md §5).
6. Ima li **vremensku komponentu** (rokovi, ponavljanje) → scheduler + kalendar.
7. Kako izgleda na **dashboardu** i šta je **pretraživo**.

Ako nešto od gore nedostaje ili je dvosmisleno — PITAJ, ne izmišljaj vrijednosti.

## Struktura foldera (kopiraj iz Tasks)

```
app/Modules/<Ime>/
  Models/<Entitet>.php            # HasFactory, Shareable, Taggable; casts; booted() emituje evente
  Enums/                          # ako entitet ima enum polja (status, priority…)
  Events/<Entitet><ŠtaSeDesilo>.php   # prošlo vrijeme; nosi samo model/ID; Dispatchable
  Listeners/                      # auto-discovered (bootstrap/app.php withEvents)
  Policies/<Entitet>Policy.php    # autorizacija kroz Shareable (isVisibleTo), ne ručni if
  Services/                       # logika (ne u Filament klasama)
  Console/                        # artisan komande (auto-registrovane iz bootstrap/app.php)
  Notifications/                  # extends App\Platform\Notifications\HouseholdNotification
  Filament/
    Resources/<Entitet>Resource.php + Pages/ (+ RelationManagers/)
    Pages/                        # custom stranice (kanban/kalendar stil), auto-discovered
    Widgets/                      # dashboard widget koji Resource koristi
  Dashboard/<Ime>DashboardWidget.php   # implements DashboardWidgetContract
  Search/<Entitet>SearchProvider.php   # implements SearchProviderContract
  Calendar/<Entitet>CalendarSource.php # implements CalendarSourceContract (ako je vremenski)
  routes/schedule.php             # ako ima periodične zadatke (vraća closure)
database/migrations/…             # prefiks tabele po modulu; household_id od prve migracije
lang/bs/<ime>.php                 # svi user-facing stringovi (nikad hardkod u UI)
tests/Feature/<Ime>/…             # Pest: CRUD, svaki event, sharing/privacy, integracija
```

## Obavezni koraci (checklist iz CLAUDE.md §14)

1. **Model** — `use HasFactory, Shareable, Taggable;` po potrebi. `household_id` +
   `created_by`. `booted()` emituje evente u `created`/`updated` (vidi Tasks
   `booted()` obrazac). Invarijante (npr. status↔completed_at) drži u `saving`.
2. **Migracije** — prefiks tabele; `household_id` FK uvijek; jedna migracija =
   jedna logička promjena. Ne mijenjaj migraciju nakon pusha na `main`.
3. **Policy** — `view/update/delete` preko `$model->isVisibleTo($user)`; Filament
   je automatski pokupi po konvenciji imena. Nikad ručni `where('household_id')`.
4. **Filament Resource** — labels preko `__('<ime>.…')`; tenancy postavlja
   `household_id` automatski, stranica postavlja `created_by`
   (`mutateFormDataBeforeCreate`). Tags/recurrence polja koja nisu kolone →
   `->dehydrated(false)` + sync u Create/Edit page hookovima.
5. **Eventi** — `<Entitet><Radnja>` u prošlom vremenu. Prije novog eventa provjeri
   `app/Platform/Events` i postojeće module da ne dupliraš.
6. **Dashboard widget** — implementira `DashboardWidgetContract` (`title`,
   `widgetClass`, `hasContentFor`). Registruj pod `dashboard_widget`.
7. **Search provider** — implementira `SearchProviderContract` (`search`, `type`),
   ograniči na household + `->visibleTo(auth()->user())`. Registruj `search_provider`.
8. **Calendar source** (ako vremenski) — implementira `CalendarSourceContract`;
   vrati `App\Platform\Calendar\CalendarEvent`. Registruj `calendar_source`.
   Tako se entitet s datumom pojavi na kalendaru bez da Calendar zna za modul.
9. **Notifikacije** — kroz `HouseholdNotification` (nikad `Mail::send`). Dopiši
   kategoriju u DATA_MODEL.md §5. Listener u `Listeners/` šalje notifikaciju.
10. **Scheduler** — `routes/schedule.php` vraća closure; ne pravi vlastiti cron.
11. **Brzo dodavanje** (opcionalno) — `quick_capture` ključ u registryju (label,
    icon, url = route name create stranice).
12. **Lokalizacija** — `lang/bs/<ime>.php`; svi stringovi kroz `__()`.
13. **Tema/responzivnost** (CLAUDE.md §6) — bez default Filament izgleda; custom
    komponente (kanban/kalendar) testirati na mobile/tablet/desktop; smislena
    prazna stanja i greške na bosanskom.
14. **App Registry** — dodaj unos u `config/homeos-apps.php`:
    ```php
    '<ime>' => [
        'name' => '…',
        'icon' => 'heroicon-o-…',
        'enabled' => true,
        'dashboard_widget' => \App\Modules\<Ime>\Dashboard\<Ime>DashboardWidget::class,
        'search_provider' => \App\Modules\<Ime>\Search\<Entitet>SearchProvider::class,
        'calendar_source' => \App\Modules\<Ime>\Calendar\<Entitet>CalendarSource::class, // ako vremenski
        'quick_capture' => ['label' => '…', 'icon' => '…', 'url' => 'filament.app.resources.<slug>.create'],
    ],
    ```

## Testovi (Pest, CLAUDE.md §16)

Minimalno: kreiranje entiteta; da emituje svaki očekivani event; da poštuje
sharing/privacy (član drugog domaćinstva ne vidi — ni kroz Resource ni kroz
scope); integracija (entitet s datumom se pojavi na dashboardu/kalendaru/pretrazi
bez ručnog povezivanja); da modul radi i kad su drugi opcioni moduli isključeni
(`config()->set('homeos-apps', [...samo ovaj...])`). Koristi `makeHousehold()`
helper iz `tests/Pest.php`. Filament testovi: `Livewire::test(...)`, uz
`Filament::setCurrentPanel(Filament::getPanel('app'))` + `Filament::setTenant(...)`.

## Verifikacija prije "gotovo"

- `php vendor/bin/pest` zeleno (lokalno u kontejneru s `intl`, i CI).
- `vendor/bin/pint` bez grešaka.
- Vizuelni pregled renderovane stranice na 3 širine (CLAUDE.md §6).
- Prođi cijelu §14 checklistu — ako se tačka svjesno preskače, navedi to u commit
  poruci i `ROADMAP.md`, ne tiho.
