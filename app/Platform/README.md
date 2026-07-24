# Platform jezgro (Faza 1)

Ovaj folder je "operativni sistem" na kojem grade svi moduli (`app/Modules/*`).
Pet ekstenzionih tačaka — svaku modul koristi **bez izmjene postojećeg koda**.
Detalji pravila: `CLAUDE.md` §7–§11.

## 1. Emitovanje eventa
Događaji su u `app/Platform/Events` (platform) ili `app/Modules/<Ime>/Events`
(modul), imenovani `<Entitet><ŠtaSeDesilo>` u prošlom vremenu, nose samo
model/ID:

```php
event(new TaskCompleted($task));
```

## 2. Slušanje tuđeg eventa (bez zavisnosti)
Listener se stavi u `app/Platform/Listeners` ili `app/Modules/<Ime>/Listeners`
i **auto-discovera** se po tipu argumenta u `handle()` (wiring u
`bootstrap/app.php` → `withEvents`). Npr. Kalendar sluša `TaskDueDateChanged`
a Zadaci ne znaju za Kalendar.

```php
class CreateCalendarEntry
{
    public function handle(TaskDueDateChanged $event): void { /* ... */ }
}
```

## 3. Slanje obavještenja (in-app + email, po preferenci)
Nikad `Mail::send` direktno. Napravi notifikaciju koja nasljeđuje
`App\Platform\Notifications\HouseholdNotification` i deklariše `category()`.
`database` kanal ide uvijek; `mail` samo ako član nije isključio tu kategoriju
(`notification_preferences`). Notifiable je `HouseholdMember`.

```php
$member->notify(new BillDueSoon($bill));
```

Kategorije se vode u `DATA_MODEL.md` §5.

## 4. Dijeljenje / privatnost (Shareable)
Model dobije `use App\Platform\Concerns\Shareable;` (i mora imati `household_id`
+ `created_by`). Nema svojih `is_private`/`visibility` kolona — sve ide kroz
`shares` tabelu.

```php
$task->shareWithHousehold();      // svi u domaćinstvu (default pri kreiranju)
$task->makePrivate();             // samo vlasnik
$task->shareWith([$member]);      // određeni članovi (+ emituje Shared → notif.)
Task::visibleTo($user)->get();    // upit ograničen na vidljivo korisniku
```

Autorizacija ide kroz Policy klasu modula koja interno zove `isVisibleTo()`
(CLAUDE.md §11).

## 5. Periodični zadatak
Modul doda `app/Modules/<Ime>/routes/schedule.php` koji vraća closure — centralni
scheduler (`bootstrap/app.php` → `withSchedule` → `ModuleSchedule`) ga pokupi,
`scheduler` kontejner izvršava. Bez svog crona.

```php
return function (Illuminate\Console\Scheduling\Schedule $schedule): void {
    $schedule->command('bills:check-due')->dailyAt('08:00');
};
```

## 6. Pretraživost
Modul napravi klasu koja implementira
`App\Platform\Contracts\SearchProviderContract` i registruje je u
`config/homeos-apps.php` pod `search_provider`. `App\Platform\Search\SearchService`
je agregira automatski (household-scoped). Isti princip za dashboard widget
(`DashboardWidgetContract`, ključ `dashboard_widget`).
