# Home OS

Lični "kućni operativni sistem" — jedna aplikacija koja objedinjuje
svakodnevnu administraciju domaćinstva (zadaci, kalendar, podsjetnici,
finansije, bilješke...), dijeljena između članova domaćinstva i izgrađena
kao platforma na koju se nove aplikacije mogu dodavati bez izmjene
postojećeg koda.

Puna specifikacija razvoja: [`CLAUDE.md`](CLAUDE.md) · redoslijed faza:
[`ROADMAP.md`](ROADMAP.md) · šema podataka: [`DATA_MODEL.md`](DATA_MODEL.md)
· izvorni brief: [`docs/ORIGINAL_SPEC.md`](docs/ORIGINAL_SPEC.md).

## Stack

Laravel · Filament v3 · MySQL/MariaDB · Redis · Laravel Reverb · Resend
(email) · Docker Compose · GitHub Actions. Potpuna lista i obrazloženje u
`CLAUDE.md` tačka 2.

## Pokretanje lokalno

Preduslovi: [Docker Desktop](https://www.docker.com/products/docker-desktop/)
(WSL2 backend na Windows-u).

```bash
cp .env.example .env
docker compose up -d --build
```

`docker-compose.override.yml` se automatski učitava uz `docker-compose.yml`
i dodaje lokalne dev alate (Xdebug, Mailpit) — nije potrebna posebna
komanda za to.

Prvo pokretanje generiše `APP_KEY` i migrira bazu automatski
(`docker/entrypoint.sh`). Aplikacija je dostupna na:

```
http://localhost:8091
```

(port se poklapa sa `APP_INTERNAL_PORT` iz `.env` — vidi `DATA_MODEL.md`
tačku 7).

Mailpit (hvata testne emailove umjesto slanja na prave adrese) je dostupan
na `http://localhost:8025`.

### Korisne komande

```bash
docker compose exec app php artisan migrate      # ručno pokretanje migracija
docker compose exec app php artisan tinker       # REPL
docker compose exec app vendor/bin/pest          # testovi
docker compose exec app vendor/bin/pint          # lint/format
docker compose logs -f queue-worker               # prati queue worker
docker compose down                               # zaustavi sve servise
```

### Prvo korištenje

1. Otvorite `http://localhost:8091` i registrujte se.
2. Nakon registracije, kreirajte svoje domaćinstvo (postajete vlasnik).
3. U sekciji "Članovi domaćinstva" možete pozvati drugog, već registrovanog
   korisnika u domaćinstvo po email adresi.

## Produkcija

Deployment lanac (Apache/Virtualmin reverse proxy, `docker-compose.prod.yml`,
GitHub Actions `deploy.yml`) opisan je u `ROADMAP.md` (Faza 0.5) i
`CLAUDE.md` (tačka 3a) — uspostavlja se nakon što je lokalni skeleton
potvrđen.

## Struktura projekta

Svaki modul (Zadaci, Kalendar, Finansije...) živi u `app/Modules/<Ime>` i
komunicira s ostatkom sistema isključivo kroz `app/Platform` (eventi,
dijeljenje, notifikacije) — nikad direktnim pristupom internom kodu drugog
modula. Potpuna konvencija: `CLAUDE.md` tačka 4.

## Testiranje

Pest, pokreće se automatski u CI-ju (`.github/workflows/ci.yml`) na svaki
push/PR ka `main`.
