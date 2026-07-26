# HomeOS plus

Lični "kućni operativni sistem" — jedna aplikacija koja objedinjuje
svakodnevnu administraciju domaćinstva (zadaci, kalendar, podsjetnici,
finansije, bilješke...), dijeljena između članova domaćinstva i izgrađena
kao platforma na koju se nove aplikacije mogu dodavati bez izmjene
postojećeg koda.

Dostupan na bosanskom, engleskom i njemačkom — jezik se bira zastavicom u
traci (i na stranici prijave), a emailovi stižu na jeziku primaoca.

## Dokumentacija

Sva dokumentacija živi u `docs/`:

| Fajl | Sadržaj |
|---|---|
| [`docs/CLAUDE.md`](docs/CLAUDE.md) | Pravila razvoja i konkretni interfejsi — čita se prije svakog rada na projektu |
| [`docs/ROADMAP.md`](docs/ROADMAP.md) | Redoslijed faza i stanje svake od njih |
| [`docs/DATA_MODEL.md`](docs/DATA_MODEL.md) | Šema podataka i konvencije imenovanja polja |
| [`docs/RULES.md`](docs/RULES.md) | Terminologija, pravopis i UX pravila korisničkog teksta |
| [`docs/SUBMISSION.md`](docs/SUBMISSION.md) | Šta je izgrađeno, fazu po fazu, s obrazloženjima odluka |
| [`docs/ORIGINAL_SPEC.md`](docs/ORIGINAL_SPEC.md) | Izvorni brief — namjera koju su ostali dokumenti razradili |

U korijenu ostaje samo `CLAUDE.md` od nekoliko linija, koji uvozi
`docs/CLAUDE.md`: Claude Code automatski čita `CLAUDE.md` iz korijena, pa bi
bez njega pravila prestala da se učitavaju sama.

## Stack

Laravel · Filament v3 · MySQL/MariaDB · Redis · Laravel Reverb · Resend
(email) · Docker Compose · GitHub Actions. Potpuna lista i obrazloženje u
`docs/CLAUDE.md` tačka 2.

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

(port se poklapa sa `APP_INTERNAL_PORT` iz `.env` — vidi `docs/DATA_MODEL.md`
tačku 7).

Mailpit (hvata testne emailove umjesto slanja na prave adrese) je dostupan
na `http://localhost:8025`.

> **Napomena (samo lokalni dev):** kod bind-mount-a cijelog projekta preko
> `docker-compose.override.yml`, PHP opcache ne provjerava da li se fajlovi
> promijenili na svaki request (`opcache.validate_timestamps=0`) — na
> Windows-u je provjera stotina vendor fajlova preko bind mounta inače
> dovoljno spora da request traje i preko 40 sekundi. Posljedica: nakon
> izmjene PHP koda potrebno je `docker compose restart app` (i
> `queue-worker`/`scheduler` ako su njih dirali) da se izmjena vidi.

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
3. U postavkama domaćinstva, sekcija "Članovi", pozovite ostale po email
   adresi — ako osoba još nema nalog, dobija pozivnicu s linkom.

## Produkcija

Deployment lanac (Apache/Virtualmin reverse proxy, `docker-compose.prod.yml`,
GitHub Actions `deploy.yml`) opisan je u `docs/ROADMAP.md` (Faza 0.5 i Faza 8)
i `docs/CLAUDE.md` (tačka 3a). Stanje instalacije provjerava javni endpoint
`/health` (baza, cache, storage i verzija).

## Struktura projekta

Svaki modul (Zadaci, Kalendar, Finansije...) živi u `app/Modules/<Ime>` i
komunicira s ostatkom sistema isključivo kroz `app/Platform` (eventi,
dijeljenje, notifikacije) — nikad direktnim pristupom internom kodu drugog
modula. Potpuna konvencija: `docs/CLAUDE.md` tačka 4.

## Testiranje

Pest, pokreće se automatski u CI-ju (`.github/workflows/ci.yml`) na svaki
push/PR ka `main`.
