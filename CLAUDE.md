# CLAUDE.md — Pravila razvoja za Home OS

Ovaj dokument čita Claude Code na početku svakog rada na ovom projektu.
Svrha: da svaki modul dodan danas ili za godinu dana bude jednako lako
uklopiv u sistem, bez potrebe za "rušenjem i prepravljanjem" starog koda.

Prati se uz `ROADMAP.md` (redoslijed faza) i `DATA_MODEL.md` (šema podataka)
— ovaj dokument su PRAVILA i KONKRETNI INTERFEJSI koji važe u svakoj fazi.

**Status:** sve globalne apstraktne odluke (UI sloj, vizuelni dizajn i
responzivnost, email provider, interfejsi, testing, autorizacija,
lokalizacija) su zaključane prije početka Faze 0. Ništa od ovoga se ne
mijenja usput bez eksplicitne odluke — ako se tokom razvoja pokaže da
nešto treba drugačije, ide kao izmjena ovog dokumenta, ne kao tiha
improvizacija u jednom modulu.

---

## 1. Vodeći principi (iz projektne specifikacije)

- **Sve je povezano** — moduli komuniciraju kroz events, ne kroz direktne
  pozive tuđeg internog koda.
- **Nova app = ravnopravan građanin** — instalira se isto kao ugrađene,
  bez posebnih izuzetaka u core kodu.
- **Ne duplirati** — nova funkcionalnost čita/koristi postojeće podatke
  (Task, Household, Notification...) umjesto da pravi paralelni sistem.
- **Domaćinstvo zadržava kontrolu** — pristup podacima se eksplicitno
  dodjeljuje, nikad implicitno.
- **Niska frikcija** — jednostavnost i brzina dodavanja stvari je prioritet
  nad teoretskom "savršenom" arhitekturom.

Kad god je nejasno kako nešto implementirati, ovih pet principa je kriterij
odlučivanja — ne lična preferenca trenutnog pristupa.

---

## 2. Tehnički stack (zaključano)

| Sloj | Izbor |
|---|---|
| Backend | Laravel (najnovija LTS) |
| UI / Admin panel | **Filament v3** |
| Baza | MySQL/MariaDB |
| Cache/Queue/Session | Redis |
| Realtime | Laravel Reverb |
| Transakcijski email | **Resend** |
| SSL / vanjski reverse proxy | **Postojeći Apache (Virtualmin)** na hostu — ne dodatni kontejner (vidi tačku 3a) |
| Interni web server (u Docker stacku) | Nginx (bez SSL, samo PHP-FPM upstream), izložen isključivo na `127.0.0.1` |
| Testing | **Pest** |
| Deployment | Docker Compose + GitHub Actions |
| Server | Postojeći Contabo VPS (Debian/Ubuntu, Virtualmin), izolovano od ostalih vhost-ova |

Ništa od ovoga se ne mijenja bez izmjene ovog dokumenta.

---

## 3a. Mrežna arhitektura na serveru (zaključano)

Server već hostuje desetine domena kroz Virtualmin/Apache (`apache2` drži
javne portove 80/443 za sve njih). Home OS se **ne** takmiči za te
portove — umjesto toga:

- `homeos.imel.cloud` je novi Virtualmin "virtual server", kreiran i
  administriran ručno (izvan Claude Code-a, vidi `ROADMAP.md` Preduslove).
  Virtualmin/Apache drži SSL sertifikat i javne portove za tu domenu, kao
  za sve ostale.
- **Baza podataka je postojeći MariaDB na hostu** (`mariadbd` na
  `127.0.0.1:3306`, potvrđeno provjerom porta), NE kontejnerizovana baza
  u produkciji — `homeosdb` baza i `homeos` korisnik su već kreirani tamo.
  Docker `docker-compose.prod.yml` stack (app + nginx + redis +
  queue-worker + scheduler) NEMA mysql servis; app kontejner se povezuje
  na hostovu bazu preko `host.docker.internal` (zahtijeva `extra_hosts:
  host.docker.internal:host-gateway` na app servisu). Lokalni dev
  (`docker-compose.yml`) i dalje ima svoj kontejnerizovan MySQL servis —
  razlika postoji samo u produkciji.
- Docker stack **nikad** ne mapira port na `0.0.0.0:80` ili `0.0.0.0:443`
  — jedini eksponirani port je interni web servis (Nginx pred PHP-FPM),
  mapiran isključivo na loopback: **`127.0.0.1:8091:80`** (port 8091
  potvrđen slobodan provjerom `ss -tlnp`).
- Apache vhost za `homeos.imel.cloud` je reverse proxy ka tom internom
  portu (`ProxyPass`/`ProxyPassReverse` na `http://127.0.0.1:8091/`).
  Ovo se dodaje kroz Virtualmin "Website Options → Edit Directives" (ili
  ekvivalentnu opciju za custom Apache direktive po virtual serveru), NE
  ručnim editovanjem `.conf` fajla direktno — Virtualmin može regenerisati
  te fajlove i prepisati ručne izmjene.
- Container unutar Docker stacka NEMA i ne treba TLS/sertifikat — SSL
  terminacija se dešava isključivo na Apache/Virtualmin nivou, prije nego
  saobraćaj uopšte stigne do Dockera.

---

## 3. Email — Resend (zaključano)

Sve transakcijske emailove (Notification sistem iz tačke 8) šalje **Resend**,
ne generički SMTP server.

- Composer paket: `resend/resend-laravel` — dodaje `resend` mail driver koji
  Laravel prepoznaje nativno preko `config/mail.php`.
- `.env`: `MAIL_MAILER=resend`, `RESEND_KEY=` (API ključ iz Resend dashboarda,
  čuva se kao GitHub Secret za produkciju, nikad u repou).
- **Domain verifikacija** (preduslov, radi se jednom u Resend dashboardu
  prije Faze 0.5): dodati **`homeos.imel.cloud`** (poddomenu, ne root
  `imel.cloud`) kao verifikovan domen u Resendu — Resend i sam preporučuje
  slanje sa poddomene radi izolacije reputacije, a ovdje dodatno znači da
  se ne dira dio DNS zone koji utiče na ostale firmine sajtove. **DNS je
  autoritativno hostovan na Hurricane Electric (`ns1-ns5.he.net`), ne
  lokalno na serveru** — iako server ima svoj Virtualmin/BIND, taj lokalni
  zapis internet ne konsultuje. SPF/DKIM zapisi (tačne vrijednosti
  generisane po domenu u Resend dashboardu) se dodaju direktno kroz
  Hurricane Electric DNS management ekran (dns.he.net), koristeći pune
  nazive relativne na `imel.cloud` (npr. `send.homeos.imel.cloud`, ne
  skraćeno `send`). Bez ovoga se email notifikacije neće moći pouzdano
  testirati u produkciji.
- `MAIL_FROM_ADDRESS` mora biti na verifikovanom domenu:
  `notifications@homeos.imel.cloud`.
- Lokalni dev **ne koristi Resend** — ostaje na `mailhog`/`mailpit` iz
  `docker-compose.override.yml` (tačka Faza 0 u `ROADMAP.md`), da se ne
  troši Resend kvota i da se ne šalju testni emailovi stvarnim adresama.
- Resend besplatni plan (trenutno 100 emaila/dan, 3000/mjesec) je dovoljan
  za obim jednog domaćinstva — ako se to promijeni, provjeriti trenutne
  limite na resend.com prije nego što se osloni na broj iz ovog dokumenta.
- Nema potrebe za dodatnim webhook handling-om (bounce/complaint) u ranim
  fazama — ako Resend to zatraži kasnije (npr. za digest emailove na više
  adresa), dodaje se kao poseban modul u `app/Platform`, ne ad-hoc kod u
  Notification klasama.

---

## 4. Struktura projekta

```
app/
  Modules/
    Tasks/
      Models/
      Events/
      Listeners/
      Filament/
        Resources/        <- TaskResource (lista, forma, tabela)
        Widgets/           <- dashboard widget ovog modula
      Policies/
      routes.php (ako treba nešto van Filamenta, npr. javni API)
    Calendar/
    Reminders/
    Notes/
    Finance/
    LifeAdmin/
  Platform/
    Models/               <- Household, HouseholdMember, Share
    Events/                <- generički platform eventi (npr. Shared)
    Contracts/              <- interfejsi iz tačke 5 i 6 (DashboardWidget,
                               SearchProvider)
    Filament/
      Widgets/              <- dashboard aggregation widget (čita iz svih
                               modula preko Contracts\DashboardWidget)
config/
  homeos-apps.php          <- App Registry (registracija modula)
docker/
  Dockerfile, nginx.conf (interni, bez SSL), docker-compose*.yml
.github/workflows/
  ci.yml, deploy.yml
lang/
  bs/                       <- prijevodi, po modulu (tasks.php, finance.php...)
```

Svaki modul je samostalan folder pod `app/Modules/<Ime>`. Modul NIKAD ne
importuje klase direktno iz drugog modula — jedina dozvoljena komunikacija
je preko `app/Platform` (eventi, sharing, notifikacije) ili preko javnog
servisa koji drugi modul eksplicitno izloži.

---

## 5. UI sloj — Filament (zaključano)

- Jedan Filament **Panel** (`admin` ili `app` panel) za cijelu aplikaciju —
  ne pravi se poseban panel po modulu.
- **Multi-tenancy kroz Filament** koristi se za `Household`: Filament
  panel je konfigurisan sa `->tenant(Household::class)`, tako da je
  household-scoping (koji podaci se prikazuju) riješen na nivou panela, a
  ne ručnim `where('household_id', ...)` u svakom Resource-u.
- Svaki modul dodaje **svoj Filament Resource** (`TaskResource`,
  `BillResource`...) unutar `app/Modules/<Ime>/Filament/Resources` — ne
  registruje se ručno, Filament ga auto-discoveruje po konvenciji foldera
  (podesiti `discoverResourcesIn` u Panel provideru da uključi
  `app/Modules/*/Filament/Resources`).
- Kanban (Faza 3) se radi kao custom Filament Page sa Livewire komponentom
  (Filament nema ugrađen kanban board), koristeći isti `Task` model/Resource
  podatke.
- Kalendar (Faza 3) — custom Filament Page + postojeći paket
  (npr. `filament/actions` + FullCalendar Livewire integracija) nad istim
  `Task`/`Reminder` modelima, ne novi model za "kalendarski događaj".
- Dashboard "Today" (Faza 2) je Filament Dashboard sa widgetima — svaki
  modul dodaje widget koji implementira `DashboardWidgetContract` (tačka 7).

---

## 6. Vizuelni dizajn i responzivnost (zaključano)

**Cilj:** sistem mora djelovati moderno, promišljeno dizajnirano i
intuitivno na svim uređajima — ne kao neizmijenjen default Filament admin
panel izgled. Ovo NIJE "polish za na kraju" (stara Faza 9 napomena) — pravilo
važi od prve stranice koja se napravi (Faza 0/0.5) i provjerava se za
svaki modul, ne samo jednom na kraju projekta.

- **Custom Filament tema** — definisati token sistem PRIJE Faze 2
  (Dashboard): 4-6 imenovanih hex boja (ne default Filament amber/indigo),
  jedan display font za naslove (korišten suzdržano, npr. samo H1/H2), jedan
  body font za ostatak, konzistentna spacing/radius skala. Tema se gradi kao
  zaseban Tailwind theme fajl kompajliran u Docker build koraku (`npm run
  build` unutar `Dockerfile`), ne inline stilovi po Resource-u.
- **Responzivnost** — mobile-first, eksplicitno testirano na minimalno 3
  širine: mobile (~375px), tablet (~768px), desktop (~1280px+). Filament je
  responzivan po defaultu za standardne Resource tabele/forme, ali custom
  komponente (Kanban board, Kalendar) MORAJU biti posebno testirane na
  mobilnom — npr. Kanban treba touch-friendly alternativu promjeni statusa
  (dropdown/action meni) ako drag & drop nije praktičan na malom ekranu.
- **Dark mode** — koristiti Filament-ovu ugrađenu podršku, uključenu, sa
  mogućnošću da član domaćinstva bira svijetlu/tamnu temu.
- **Pristupačnost (osnova)** — vidljiv keyboard focus na svim interaktivnim
  elementima, poštovanje `prefers-reduced-motion`, kontrast boja minimalno
  po WCAG AA standardu. Ovo je dio "definition of done" svakog modula, ne
  opcionalan naknadni prolaz.
- **Prazna stanja i greške** — svaki modul ima smislen tekst za prazno
  stanje (šta uraditi, ne generički "No results") i za greške (šta se
  desilo i sljedeći korak), na bosanskom, u aktivnom glasu, dosljedan
  terminologijom kroz cijelu aplikaciju (isti pojam za istu radnju svuda).
- **Signature element** — dashboard "Today" prikaz treba imati jedan
  prepoznatljiv vizuelni element (ne generički grid kartica) koji ga čini
  prepoznatljivim — odlučiti prilikom dizajna teme, prije Faze 2.
- **Vizuelna provjera** — prije nego se faza koja uvodi UI (Faza 2+) smatra
  gotovom, renderovanu stranicu treba i vizuelno pregledati (screenshot na
  sve 3 širine iz tačke responzivnosti), ne samo provjeriti da funkcionalni
  testovi prolaze.

---

## 7. Interfejs: Dashboard Widget (zaključano, Faza 1)

Svaki modul koji ima nešto za prikazati na "Today" dashboardu implementira:

```php
namespace App\Platform\Contracts;

interface DashboardWidgetContract
{
    /** Naziv koji se prikazuje kao naslov sekcije widgeta */
    public function title(): string;

    /** Filament Widget klasa koja se renderuje na dashboardu */
    public function widgetClass(): string;

    /** Da li widget ima šta prikazati za dato domaćinstvo (za prazna stanja) */
    public function hasContentFor(Household $household): bool;
}
```

Modul registruje svoju implementaciju u `config/homeos-apps.php` pod
`dashboard_widget` ključem (vidi tačku 10). Core dashboard NE zna ništa o
Tasks/Finance/... — samo iterira registrovane widgete i renderuje ih.

---

## 8. Interfejs: Search Provider (zaključano, Faza 1)

```php
namespace App\Platform\Contracts;

interface SearchProviderContract
{
    /** Vraća kolekciju rezultata (id, title, url, icon) za dati upit,
     *  ograničeno na dato domaćinstvo */
    public function search(string $query, Household $household): \Illuminate\Support\Collection;

    /** Ključ tipa rezultata, npr. 'task', 'note' — za grupisanje u UI */
    public function type(): string;
}
```

Isti princip — core search agregira rezultate svih registrovanih providera,
ne zna pojedinačno za module.

---

## 9. Event-driven pravilo (obavezno)

Kad modul uradi nešto što bi moglo biti bitno drugima, mora emitovati
Laravel event — čak i ako trenutno niko ne sluša.

```php
event(new TaskDueDateSet($task));
```

Drugi modul (npr. Calendar) reaguje kroz svoj Listener, registrovan u
`EventServiceProvider` — Calendar tako "zna" za Tasks, ali Tasks NIKAD ne
zna za Calendar. Ovo je smjer zavisnosti koji se ne smije obrnuti.

**Pravilo imenovanja:** `<Entitet><ŠtaSeDesilo>` u prošlom vremenu —
`TaskCompleted`, `BillDueSoon`, `MemberInvited`. Event nosi samo ID/model,
ne cijeli kontekst — listener sam dohvata šta mu treba.

**Prije pisanja novog modula:** provjeriti listu postojećih eventa u
`app/Platform/Events` — ako event za tu situaciju već postoji, koristiti ga
umjesto pravljenja duplikata.

---

## 10. Notifikacije

Sve notifikacije (email + in-app) idu kroz Laravel Notification klase u
`app/Platform/Notifications` ili modul-specifične notifikacije koje
implementiraju zajednički obrazac iz Faze 1. Modul ne šalje email direktno
(`Mail::send(...)`) — uvijek kroz Notification sistem, jer samo tako radi
korisničko uključivanje/isključivanje kategorija (Faza 6, vidi
`DATA_MODEL.md` tačku 5 za listu kategorija).

---

## 11. Sharing / privatnost i autorizacija

- Svaki model čiji objekti mogu biti privatni/dijeljeni MORA koristiti
  zajednički `Shareable` trait/mehanizam iz `app/Platform` (vidi
  `DATA_MODEL.md` tačku 2) — nikad vlastito `is_private` polje.
- **Autorizacija ide kroz Laravel Policy klase**, jedna Policy po glavnom
  modul-entitetu (`TaskPolicy`, `BillPolicy`...), smještena u
  `app/Modules/<Ime>/Policies`. Policy interno poziva Shareable mehanizam
  (`can('view', $task)` → provjerava `shares` tabelu) — nikad ručna
  `if` provjera raštrkana po kontrolerima/Filament Resource-ima. Filament
  Resource automatski koristi Policy ako postoji (Laravel konvencija
  imenovanja) — dodatna konfiguracija nije potrebna.

---

## 12. App Registry (Faza 7, `config/homeos-apps.php`)

```php
return [
    'tasks' => [
        'name' => 'Zadaci',
        'icon' => 'heroicon-o-check-circle',
        'dashboard_widget' => \App\Modules\Tasks\DashboardWidget::class, // implementira DashboardWidgetContract
        'search_provider' => \App\Modules\Tasks\Search\TaskSearchProvider::class, // implementira SearchProviderContract
        'enabled' => true, // household može ovo ugasiti po sebi (Faza 7)
    ],
];
```

Core (dashboard, search, navigacija) čita isključivo iz ovog fajla — nikad
hardkodovana lista modula u Blade/Filament kodu.

---

## 13. Lokalizacija

- Aplikacija je primarno na bosanskom (`APP_LOCALE=bs`).
- Prijevodi po modulu: `lang/bs/tasks.php`, `lang/bs/finance.php`... — modul
  nosi svoje prijevode u istoj strukturi kao i ostatak koda, ne u jednom
  monolitnom fajlu.
- Filament Resource labels, navigation grupe i sl. koriste `__('tasks.title')`
  stil, ne hardkodovan tekst — radi buduće mogućnosti drugog jezika.

---

## 14. Checklist za dodavanje nove "app" (modula)

Prije nego što se modul smatra gotovim, mora ispuniti sve tačke:

- [ ] Folder pod `app/Modules/<Ime>`, prati strukturu iz tačke 4
- [ ] Registrovan u `config/homeos-apps.php`
- [ ] Koristi postojeći `Household`/`HouseholdMember` model, Filament tenant
      scoping — ne pravi svoj koncept korisnika/vlasnika
- [ ] Ako ima objekte koji mogu biti privatni/dijeljeni → Shareable
      mehanizam (tačka 11)
- [ ] Ima Policy klasu za glavni entitet (tačka 11)
- [ ] Ako šalje obavještenja → Notification sistem (tačka 10), ne direktan
      mail
- [ ] Ako radi nešto vremenski (rokovi, ponavljanje) → registruje se u
      centralni Scheduler, ne pravi svoj cron
- [ ] Emituje relevantne evente za akcije koje bi mogle zanimati druge
      module (tačka 9)
- [ ] Implementira `DashboardWidgetContract` (tačka 7) — može biti
      minimalan/prazan widget, ali mora postojati
- [ ] Implementira `SearchProviderContract` (tačka 8)
- [ ] Filament Resource prati konvenciju auto-discovery iz tačke 5
- [ ] Prati custom Filament temu i responzivnost/pristupačnost pravila iz
      tačke 6 — testiran na mobile/tablet/desktop, prazna stanja i greške
      imaju smislen tekst
- [ ] Prijevodi u `lang/bs/<modul>.php` (tačka 13)
- [ ] Radi (ne baca grešku) i kad su drugi opcioni moduli isključeni —
      testirati eksplicitno (Pest test)
- [ ] Pest testovi za osnovni CRUD i za svaki emitovan event
- [ ] Migracije imaju prefiks tabele po modulu (npr. `tasks_`, `finance_`)

Ako se doda nova app a neka tačka se svjesno preskače, to mora biti
eksplicitno navedeno u commit poruci i u `ROADMAP.md` napomeni, ne tiho
izostavljeno.

---

## 15. Konvencije koda

- Laravel konvencije (PSR-12).
- Migracije: jedna migracija = jedna logička promjena, opisno ime fajla.
- Logika ide u Service/Action klase, ne u Filament Resource/Page klase
  direktno (one pozivaju servise).
- Svaka nova tabela ima `household_id` foreign key osim ako je eksplicitno
  globalna — multi-tenant izolacija je obavezna od prve migracije.
- Env varijable se dodaju u `.env.example` ISTOVREMENO kad se dodaju u kod.
- Nazivi polja prate `DATA_MODEL.md` tačku 3 (npr. uvijek `due_date`, nikad
  `deadline_at`/`due_at` kao sinonim).

---

## 16. Testiranje (Pest)

- Svaki modul ima minimalno: test kreiranja glavnog entiteta, test da
  emituje očekivani event, test da poštuje sharing/privacy pravila (član
  drugog domaćinstva ne smije vidjeti podatke — ni kroz Filament Resource
  ni kroz API).
- CI (GitHub Actions) mora proći prije mergea u `main`.
- Ne pisati testove koji zavise od trenutnog vremena bez
  `Carbon::setTestNow()` / Pest `travel()` helpera.
- Filament Resource testovi koriste `livewire()` test helper
  (`Livewire::test(TaskResource\Pages\ListTasks::class)`).

---

## 17. Git i deployment workflow

- Grane: `main` (produkcija, auto-deploy), feature grane po zadatku.
- Commit poruke: kratak opis + koji modul/fazu iz `ROADMAP.md` pokrivaju.
- Migracije se nikad ne mijenjaju nakon što su pushane na `main` — nova
  migracija za ispravku.
- Deploy je automatski na push u `main` (vidi `ROADMAP.md` Faza 8) — `main`
  mora uvijek biti u stanju spremnom za produkciju (CI zeleno).
- Prije prve produkcijske migracije koja mijenja postojeću tabelu na
  serveru: backup (dio `deploy.yml`).

---

## 18. Šta NIKAD ne raditi

- Ne pristupati direktno bazi/modelu drugog modula zaobilazeći njegov javni
  interfejs (Service klasu ili event).
- Ne hardkodovati listu modula u dashboard/search/navigaciji — uvijek čitati
  iz `config/homeos-apps.php`.
- Ne dirati postojeće produkcijske vhost-ove na serveru (server već
  hostuje desetine drugih domena kroz Apache/Virtualmin — puna lista je
  interna, ne navodi se u ovom javnom repou) — Home OS ima svoje izolovane
  kontejnere, portove i bazu/user.
- Ne slati email mimo Notification sistema.
- Ne praviti privatno/dijeljeno polje mimo Shareable mehanizma.
- Ne raditi ručni household-scoping (`where('household_id', ...)`) mimo
  Filament tenancy sloja — ako se to dešava, znak je da tenancy konfiguracija
  nije ispravno postavljena, popraviti tamo, ne zaobilaziti po Resource-ima.
- Ne pisati hardkodovan tekst u Filament UI — sve ide kroz `lang/bs/*`.
- Ne ostavljati default Filament izgled (default paleta/font) niti isporučiti
  UI koji nije provjeren na mobilnoj širini — vizuelni dizajn i
  responzivnost (tačka 6) je dio "definition of done", ne naknadni "polish".
- Ne mapirati Docker kontejner na javni port `0.0.0.0:80`/`0.0.0.0:443` —
  te portove drži Apache/Virtualmin za sve domene na serveru (tačka 3a).
  Kontejner sluša isključivo na `127.0.0.1:<interni-port>`.

---

## 19. Kad nešto nije pokriveno ovim dokumentom

Vratiti se na principe u tački 1. Ako ni to ne daje jasan odgovor, provjeriti
`docs/ORIGINAL_SPEC.md` — izvorni brief nosi namjeru koju su ROADMAP/CLAUDE/
DATA_MODEL operativno razradili, pa nejasnoća često nestane kad se pročita
originalna formulacija. Ako ni to ne pomogne, dodati kratku napomenu u
`ROADMAP.md` uz otvoreno pitanje umjesto tihog donošenja odluke koja može
biti pogrešna za buduće module.
