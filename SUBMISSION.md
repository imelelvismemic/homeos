<!--
Ovaj dokument je namijenjen ljudima koji ocjenjuju rješenje, ne Claude Code
agentu. Za operativni rad na projektu vrijede ROADMAP.md, CLAUDE.md,
DATA_MODEL.md i docs/ORIGINAL_SPEC.md.

Dijelovi označeni [POPUNITI ...] se dopunjuju kako projekat napreduje kroz
faze iz ROADMAP.md — ovaj dokument se ažurira usput, ne piše se tek na
kraju.
-->

# Home OS — Sažetak rješenja

**Autor:** Elvis Memić
**Alat:** Claude Code (kompletna izrada, od arhitekture do deploymenta)

---

## Šta je zadatak tražio

Lični "kućni operativni sistem" — jedna aplikacija koja objedinjuje
svakodnevnu administraciju domaćinstva (zadaci, kalendar, podsjetnici,
finansije, bilješke...), dijeljena između članova domaćinstva, izgrađena
kao **platforma** na koju se mogu dodavati nove aplikacije bez izmjene
postojećeg koda. Zadatak je dobijen kao PDF na engleskom, bez definisane
tehnologije i bez smjernica za deployment.

Puni izvorni tekst: `docs/ORIGINAL_SPEC.md` (prijevod).

---

## Rok i obim

Zadatak primljen srijeda 16h, rok predaje ponedjeljak 16h. Rad je uključio
i vikend (subota, nedjelja) — pet radnih dana ukupno umjesto tri, što je
omogućilo da se zadrži **pun obim** svih funkcionalnih modula iz brief-a,
bez unaprijed isključenih dijelova. Redoslijed rada (`ROADMAP.md`) je i
dalje postavljen po prioritetu — Faza 1 (platform jezgro), Faza 3
(Zadaci+Kanban+Kalendar) i Faza 7 (extensibility dokaz) su rađeni prvi, jer
najviše nose ciljeve izvedene ispod — ali kao redoslijed sigurnosti u
slučaju kašnjenja, ne kao trajno isključivanje modula.

---

## Ciljevi koje sam izvukao iz brief-a

Brief navodi osam funkcionalnih modula i detaljan set principa za
proširivost. Umjesto da ih tretiram kao ravnopravnu listu stavki za
"otkvačiti", izdvojio sam četiri cilja koja, po mom čitanju, nose najveću
težinu:

1. **Sistem mora dokazano djelovati povezano, ne samo deklarativno.**
   Brief eksplicitno kaže da je to "poenta" — jedan račun koji stvarno
   kreira zadatak, koji se stvarno pojavi u kalendaru, koji stvarno pokrene
   podsjetnik i email, jedan kroz drugi, bez ručnog povezivanja u svakom
   modulu.
2. **Proširivost mora biti dokazana, ne samo arhitektonski omogućena.**
   Brief detaljno razrađuje kako nova app treba da se uklopi bez diranja
   postojećeg koda — ovo tretiram kao zahtjev da se to i pokaže na
   konkretnom primjeru, ne samo opiše.
3. **Deployment je prvorazredni dio isporuke**, iako je zadan bez ikakvih
   smjernica — tretiram to kao namjernu provjeru samostalnosti, ne kao
   sporednu tehničku formalnost ostavljenu za kraj.
4. **Niska frikcija i domaćinstvo-na-prvom-mjestu** (iz "Guiding
   principles" dijela brief-a) kao kriterij za svaku UX odluku usput, ne
   samo kao završna napomena.

---

## Arhitektonske odluke i obrazloženje

Tehnologija nije bila zadana, pa su odluke donesene na osnovu: (a) postojećeg
znanja i infrastrukture koje već imam, radi realnog roka i pouzdanosti, i
(b) prirodne podudarnosti alata s onim što brief traži.

| Odluka | Obrazloženje |
|---|---|
| **Laravel** (backend) | Poznat stack; ima ugrađene mehanizme (Events, Notifications, Scheduler, Queues) koji direktno odgovaraju "sve je povezano" zahtjevu iz brief-a — nije trebalo graditi event-bus od nule. |
| **Filament v3** (UI) | Brz razvoj CRUD/dashboard/kanban ekrana; ugrađena multi-tenancy podrška mapirana direktno na koncept "domaćinstva" iz brief-a. |
| **MySQL + Redis** | Poznat stack, nema dodatnog troška. |
| **Resend** (email) | Transakcijski email bez upravljanja SMTP serverom; besplatan tier dovoljan za obim jednog domaćinstva. |
| **Docker + GitHub Actions** | Izolacija od desetina postojećih produkcijskih vhost-ova na istom serveru (server već hostuje mnoge domene preko Virtualmin-a); deploy opisan kao kod, ponovljiv i reverzibilan (`git revert` + redeploy). |
| **Postojeći Apache (Virtualmin)** kao reverse proxy | Server već drži SSL i rutiranje za sve postojeće domene — Docker stack izlaže samo interni port, Apache prosljeđuje saobraćaj; nema dupliranja SSL sloja. |
| **Postojeći Contabo VPS** (Debian/Ubuntu, Virtualmin) | Dodatni trošak = 0; deployment odluka nije prepuštena za kraj nego testirana rano (vidi niže). |

Potpuno obrazloženje i sve zaključane apstrakcije: `CLAUDE.md`.

---

## Proces rada s Claude Code

Prije nego što je napisana ijedna linija koda, definisana su četiri
dokumenta koja Claude Code prati kroz cijeli razvoj:

- **`ROADMAP.md`** — 11 faza od praznog scaffolding-a do produkcije, svaka
  sa jasnim "definition of done" kriterijem.
- **`CLAUDE.md`** — pravila razvoja i konkretni interfejsi (event-driven
  komunikacija između modula, sharing/permission mehanizam, dizajn sistem,
  checklist za svaki novi modul) — cilj je da svaki modul, uključujući onaj
  koji izgradi neko drugi ili sam Claude Code kasnije, bude jednako
  jednostavno uklopiv.
- **`DATA_MODEL.md`** — šema podataka zaključana unaprijed, da moduli ne
  izmišljaju paralelne konvencije za iste koncepte.
- **`docs/ORIGINAL_SPEC.md`** — izvorni brief, referenca za namjeru kad
  operativni dokumenti ne daju jasan odgovor.

Namjera ovog pristupa: da se apstraktne/arhitekturne odluke donesu
promišljeno i jednom, prije izrade — a ne da ih Claude Code improvizuje
modul-po-modul usput, što bi vodilo nekonzistentnom sistemu.

---

## Deployment

- Poddomena: `homeos.imel.cloud`
- Rani probni deploy (prazan skeleton, prije platform jezgra) namjerno
  urađen u Fazi 0.5 roadmap-a — da se problemi sa serverom/SSL-om/CI-jem
  otkriju prije nego je sistem izgrađen, ne poslije.
- **Live URL:** https://homeos.imel.cloud (prazan skeleton uživo — login,
  registracija, kreiranje domaćinstva, pozivanje člana, reset lozinke sa
  email obavještenjem preko Resend-a)
- **CI/CD:** GitHub Actions — CI (Pint + Pest) na svaki push/PR; deploy se
  automatski pokreće tek nakon zelenog CI-ja na `main` (SSH → git pull →
  `docker compose -f docker-compose.prod.yml up -d --build` → migrate →
  health-check na internom portu). Testirani rollback dolazi u Fazi 8.
- **Mrežna arhitektura:** Docker stack izložen samo na `127.0.0.1:8091`;
  postojeći Apache/Virtualmin drži SSL i reverse-proxya na taj port. Baza je
  postojeći hostov MariaDB (bez kontejnerizovane baze u produkciji), dostupan
  kontejnerima preko `host.docker.internal`. Potpuno izolovano od ostalih
  domena na serveru.

---

## Dokaz proširivosti (extensibility)

[POPUNITI nakon Faze 7 — ROADMAP.md]

Plan dokaza: dodati probnu "dummy" aplikaciju prateći checklist iz
`CLAUDE.md` (tačka 14) i pokazati da se pojavi na dashboardu, u pretrazi i
navigaciji bez izmjene postojećeg koda. Ovdje će biti opisan konkretan
primjer i (ako je moguće) kratak snimak/screenshot postupka.

---

## Status projekta

**Faza 0 završena** — Laravel 13 + Filament v3 skeleton, Docker Compose lokalni
dev, `Household`/`HouseholdMember` modeli, Filament auth + tenant registracija
+ invite-member UI, GitHub Actions CI, Pest testovi. Kroz lokalno testiranje
dorađeno: custom-tema/Tailwind, dev performanse (vendor van bind mounta,
opcache), bosanski prijevodi (forme, tabela, email), automatsko generisanje
APP_KEY-a.

**Faza 0.5 završena** — prazan skeleton uspješno deployan na
`https://homeos.imel.cloud` i potvrđena cijela produkcijska putanja:
- Docker produkcijski stack (`docker-compose.prod.yml`) bez kontejnerizovane
  baze — koristi hostov MariaDB preko `host.docker.internal`; Nginx samo na
  `127.0.0.1:8091`, Apache/Virtualmin SSL + reverse proxy.
- Automatski deploy (`deploy.yml`) radi kraj-do-kraja: push na `main` → CI
  zeleno → deploy zeleno (git pull → build → migrate → health-check), bez
  uticaja na ostale domene.
- Uživo provjereno: registracija, kreiranje domaćinstva, pozivanje člana,
  reset lozinke + dostava emaila preko Resend-a.

**Faza 1 završena** — platform jezgro na kojem grade svi budući moduli, pet
ekstenzionih tačaka koje modul koristi bez izmjene postojećeg koda (`CLAUDE.md`
§7–§11, vodič u `app/Platform/README.md`):
- **Eventi** — listener auto-discovery (`bootstrap/app.php`) kroz
  `app/Platform/Listeners` i `app/Modules/*/Listeners`; generički `Shared` event.
- **Notifikacije** — kanali `mail` + `database`, preferencije po članu i
  kategoriji; osnovna klasa `HouseholdNotification` bira kanale (in-app uvijek,
  email osim ako je član isključio). Notifiable je `HouseholdMember`.
- **Dijeljenje/privatnost** — generički `Shareable` trait + `shares`/
  `share_recipients` tabele (privatno / cijelo domaćinstvo / određeni članovi),
  sa izolacijom između domaćinstava; autorizacija ide kroz Policy → `isVisibleTo`.
- **Scheduler** — modul registruje periodični zadatak preko
  `routes/schedule.php`, centralni `ModuleSchedule` ga pokupi.
- **Pretraga** — `SearchProviderContract` + `SearchService` agregira providere
  iz `config/homeos-apps.php` (uz `DashboardWidgetContract` za Fazu 2).

Dokaz "sve je povezano": bilo koji `Shareable` objekat podijeljen s članom
automatski pokrene `Shared` event → platform listener → `shared_with_you`
in-app + email obavještenje (uz poštovanje preferenci) — bez ijedne linije koda
u modulu. Testirano: 18 testova / 63 assertiona; CI zeleno; deployano na
produkciju (aditivne migracije).

Sljedeće: **Faza 2** (Dashboard — dizajn token sistem, "Today" prikaz, widget
agregacija, quick capture) — čeka potvrdu prije početka.
