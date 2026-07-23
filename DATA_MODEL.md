# DATA_MODEL.md — Osnovna šema podataka i env varijable

Ovaj dokument zaključava temeljne entitete i nazive polja PRIJE početka
Faze 0/1 iz `ROADMAP.md`. Svi budući moduli (Faza 3+) nasljeđuju ove nazive
i konvencije — ne izmišljaju svoje. Ako modulu treba polje koje liči na
nešto odavde (npr. "rok"), koristi isto ime (`due_date`), ne sinonim.

Prateći dokumenti: `ROADMAP.md` (redoslijed), `CLAUDE.md` (pravila razvoja).

---

## 1. Core entiteti (Faza 0-1, `app/Platform`)

### `users`
Standardni Laravel auth model (Breeze/Fortify).
- `id`, `name`, `email`, `password`, `timezone` (string, npr. `Europe/Sarajevo`), `locale` (string, default `bs`), `timestamps`

### `households`
- `id`
- `name` (npr. "Memić domaćinstvo")
- `owner_id` → `users.id` (ko je kreirao domaćinstvo)
- `timestamps`

### `household_members` (pivot: user ↔ household)
- `id`
- `household_id` → `households.id`
- `user_id` → `users.id`
- `role` (enum: `owner`, `member`) — samo za administrativne radnje
  (brisanje domaćinstva, uklanjanje članova); NIJE granularni permission
  sistem, taj ide kroz Sharing (tačka 2)
- `joined_at`
- `timestamps`

Napomena: jedan `user` može biti član više domaćinstava (npr. odrasla djeca
sa svojim domaćinstvom, ali još vezana za roditeljsko). Svaka sesija ima
"aktivno domaćinstvo" (`session` ili `users.current_household_id`).

### `notification_preferences`
- `id`
- `household_member_id` → `household_members.id`
- `category` (string — npr. `task_assigned`, `bill_due`, `reminder_fired`,
  `shared_with_you`; lista kategorija raste kako moduli rastu, vidi tačku 5)
- `email_enabled` (bool, default `true`)
- `digest_enabled` (bool, default `false`) — uključen u dnevni/sedmični digest
- `timestamps`

---

## 2. Sharing / privatnost (Faza 1, `Shareable` mehanizam)

Generička, polymorphic tabela — svaki modul je koristi, niko ne pravi svoju.

### `shares`
- `id`
- `shareable_type` (string, polymorphic — npr. `App\Modules\Tasks\Models\Task`)
- `shareable_id` (unsigned big int, polymorphic)
- `household_id` → `households.id` (u kom domaćinstvu objekat "živi")
- `visibility` (enum: `private`, `household`, `specific`)
- `owner_id` → `users.id` (ko je vlasnik/kreator objekta)
- `timestamps`

### `share_recipients` (samo kad je `visibility = specific`)
- `id`
- `share_id` → `shares.id`
- `household_member_id` → `household_members.id`

**Pravilo:** svaki model koji treba biti privatan/dijeljen ima `use Shareable;`
trait (definisan u Faza 1) koji upravlja ovom relacijom automatski —
migracija tog modula NE dodaje svoje `is_private`/`visibility` kolone.

---

## 3. Zajednička polja koja svaki modul entitet treba imati

Da bi platform mehanizmi (dashboard widget, search, sharing, events) radili
generički, svaki "glavni" entitet modula (Task, Note, Bill, Reminder...)
treba imati:

| Polje | Tip | Napomena |
|---|---|---|
| `household_id` | FK → households | obavezno, multi-tenant izolacija |
| `created_by` | FK → users | ko je kreirao |
| `title` | string | naslov/kratak opis — koristi ovo ime, ne `name`/`subject` |
| `due_date` | nullable datetime | ako entitet ima rok (Task, Bill, Reminder) — uvijek ovo ime |
| `completed_at` | nullable datetime | ako entitet ima stanje završeno/nezavršeno |
| `timestamps` | | |

Tabele modula imaju prefiks po modulu: `tasks_*`, `finance_*`, `notes_*`
(vidi CLAUDE.md tačku 6, posljednja stavka checklist-e).

---

## 4. Primjer: `Task` (Faza 3, `app/Modules/Tasks`)

```
tasks
  id
  household_id      → households.id
  created_by        → users.id
  assigned_to        nullable FK → household_members.id
  title              string
  description        text, nullable
  priority           enum: low, medium, high
  due_date           nullable datetime
  completed_at       nullable datetime
  parent_task_id     nullable FK → tasks.id   (sub-tasks)
  recurrence_rule    nullable string           (RFC5545 RRULE ili slično)
  timestamps

tasks_tags        (id, task_id, name)   -- ili generički tags paket
```

Ovo je referentni primjer kako izgleda "modul entitet" koji poštuje tačku 3
— svaki naredni modul (Bill, Reminder, Note) prati isti obrazac.

---

## 5. Lista notifikacijskih kategorija (raste kroz faze, popuniti pri dodavanju)

Faza 1 definiše mehanizam; svaki modul pri dodavanju upisuje ovdje svoju
kategoriju da se zna šta postoji (izbjegava duplikate kao `bill_due` i
`bill_reminder` za istu stvar):

- `task_assigned` — Faza 3
- `task_due_soon` — Faza 3
- `bill_due` — Faza 5
- `reminder_fired` — Faza 4
- `shared_with_you` — Faza 1 (generička, radi za bilo koji Shareable objekat)
- `digest_daily` / `digest_weekly` — Faza 6

---

## 6. App Registry šema (Faza 7, `config/homeos-apps.php`)

Format u kojem se modul "prijavljuje" platformi:

```php
return [
    'tasks' => [
        'name' => 'Zadaci',
        'icon' => 'heroicon-o-check-circle',
        'route' => 'tasks.index',
        'dashboard_widget' => \App\Modules\Tasks\Widgets\TodayTasksWidget::class,
        'search_provider' => \App\Modules\Tasks\Search\TaskSearchProvider::class,
        'enabled' => true, // household može ovo ugasiti po sebi (Faza 7)
    ],
    // ...
];
```

---

## 7. Env varijable (`.env.example`)

Popuniti odmah u Fazi 0, prije prve migracije:

```
APP_NAME="Home OS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Europe/Sarajevo
APP_LOCALE=bs
APP_URL=http://localhost
# Produkcija: APP_URL=https://homeos.imel.cloud (postavlja se u deploy.yml / server .env, ne u repo)

DB_CONNECTION=mysql
DB_HOST=mysql
# Produkcija: DB_HOST=host.docker.internal — baza je VEĆ POSTOJEĆI MariaDB
# na hostu (127.0.0.1:3306), ne kontejnerizovana baza. docker-compose.prod.yml
# mora imati "extra_hosts: - host.docker.internal:host-gateway" na app
# servisu da kontejner uopšte može doći do hostovog loopback-a. Lokalni dev
# i dalje koristi kontejnerizovan MySQL servis (DB_HOST=mysql).
DB_PORT=3306
DB_DATABASE=homeos
DB_USERNAME=homeosdb
DB_PASSWORD=

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

MAIL_MAILER=resend
RESEND_KEY=
MAIL_FROM_ADDRESS="notifications@homeos.imel.cloud"
MAIL_FROM_NAME="${APP_NAME}"
# Lokalni dev: MAIL_MAILER=smtp, MAIL_HOST=mailpit (vidi CLAUDE.md tačku 3)

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080

# Interni port na kom Nginx u Docker stacku sluša (mapiran na 127.0.0.1
# na hostu; Apache/Virtualmin radi reverse proxy ka ovom portu — vidi
# CLAUDE.md tačku 3a). Nema TLS konfiguracije u Docker stacku.
APP_INTERNAL_PORT=8091

# Produkcija (deploy.yml koristi ove, dodati kao GitHub Secrets, ne u repo):
# DEPLOY_SSH_HOST, DEPLOY_SSH_USER, DEPLOY_SSH_KEY, DEPLOY_PATH, RESEND_KEY
```

**Napomena o produkciji:** `DB_DATABASE`/`DB_USERNAME` u produkciji moraju
biti potpuno odvojeni od baza koje već koriste postojeći produkcijski
vhost-ovi (interna lista, ne navodi se u ovom javnom repou) na istom
Contabo serveru — nov, izolovan MySQL user sa pristupom samo `homeos`
bazi (vidi CLAUDE.md tačku 10). Baza je već kreirana na postojećem
MariaDB procesu na hostu (potvrđeno: `mariadbd` sluša na
`127.0.0.1:3306`) — Docker stack u produkciji NEMA sopstveni MySQL
kontejner, povezuje se na hostovu bazu preko `host.docker.internal`.

---

## 8. Kad dodavati novi entitet u ovaj dokument

Svaki put kad Faza 3+ uvede novi "glavni" entitet modula (Bill, Note,
Reminder...), njegova šema se dopisuje ovdje po obrascu iz tačke 4, PRIJE
pisanja migracije — ne retroaktivno. Ovo održava dokument kao izvor istine
za cijelu šemu, ne samo za Fazu 0-1.
