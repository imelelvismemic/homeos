# Pravila prijevoda i terminologije (bosanski)

Ovaj dokument fiksira kako se piše korisnički tekst u Home OS-u, da bi cijela
aplikacija zvučala kao jedan proizvod, a ne kao skup nezavisnih modula. Vrijedi
za **sav** tekst vidljiv korisniku (Filament labeli, dugmad, naslovi, prazna
stanja, greške, obavještenja). Povezano s `CLAUDE.md` §6 i §13.

Nastao je iz QA prolaza nakon Faze 3 — svaki novi modul ga mora poštovati, a
`.claude/skills/homeos-new-module` upućuje na njega.

## 1. Jezik i lokalizacija

- Sve ide kroz `__('modul.kljuc')` — **nikad** hardkodovan tekst u Blade/PHP-u.
- Prijevodi po modulu u `lang/bs/<modul>.php`.
- Paketske (Filament) prijevode koji su pogrešni, nedostaju ili su na engleskom
  ispravljamo kroz `lang/vendor/<paket>/bs/<fajl>.php` (Laravel spaja override
  rekurzivno preko paketskog fajla — dovoljno je navesti samo izmijenjene
  ključeve). Postojeći primjeri: `filament-panels`, `filament-actions`,
  `filament-tables`, `filament-forms`.
- **Provjeriti tačan ključ u paketu**, ne pogađati ga. Primjer iz QA prolaza
  prije Faze 7: meni za prikaz/skrivanje kolona vuče
  `tables::table.column_toggle.heading` (a ne `column_manager.heading`), pa je
  na svim listama pisalo englesko "Columns" iako je override postojao. Ključ se
  potvrđuje u `vendor/filament/<paket>/resources/lang/en/…`, a onda se doda u
  `lang/vendor/…/bs/…`. Prijevod tada važi za **sve** module odjednom — ako se
  isti tekst "vraća na engleski" u novom modulu, znak je da je ključ pogrešan,
  ne da ga treba popravljati po Resource-u.
- Laravel validacijske poruke su prevedene u `lang/bs/validation.php` (bez toga
  se miješa engleski na formama, npr. registracija/obnova šifre).

## 2. Veliko/malo slovo (pravopis)

- **Rečenice i naslovi**: veliko slovo samo na **prvoj** riječi i vlastitim
  imenima. Druga riječ ide malim slovom.
  - ✅ `Dodaj zadatak`, `Uredi zadatak`, `Brisanje zadatka`, `Novi zadatak`
  - ❌ `Dodaj Zadatak`, `Kreiraj Podzadatak`
- **Naziv modula u navigaciji** je vlastiti naziv sekcije i piše se veliko:
  `Zadaci`, `Kalendar`, `Kanban`.
- Zbog toga Filament `getModelLabel()`/`getPluralModelLabel()` mogu ostati
  "Zadatak"/"Zadaci" (za nav/liste), ali naslove stranica koji umeću labelu u
  rečenicu (`Dodaj :label`) postavljamo eksplicitno malim slovom
  (`getTitle()` → `tasks.headings.create`).

## 3. Terminologija dugmadi (imperativ, dosljedno svuda)

| Radnja | Termin | Ne koristiti |
|---|---|---|
| Snimanje | **Sačuvaj** | Saćuvaj, Napraviti, Snimi |
| Zatvaranje/odustajanje | **Zatvori** | Prekini, Prekinit, Prekid, Prekiniti |
| Snimi + novi unos | **Sačuvaj i dodaj novi** | Napravi i napravi još jedan, Kreiraj i kreiraj još jedan |
| Dodavanje novog | **Dodaj \<šta\>** / **Kreiraj \<šta\>** | Kreirajte, Napravi |
| Brisanje | **Obriši** | Izbriši (dosljedno "Obriši") |
| Potvrda | **Potvrdi** | — |

- Dugmad su u **imperativu jednine** (`Sačuvaj`, `Zatvori`, `Obriši`), ne
  infinitivu (`Sačuvati`, `Zatvoriti`).

**Imenice koje se ponavljaju kroz sistem:**

| Pojam | Termin | Ne koristiti |
|---|---|---|
| Pristupna riječ naloga | **lozinka** | šifra |
| Član domaćinstva zadužen za stavku | **odgovorna osoba** | zaduženi, izvršilac |
| Rok / vrijeme dospijeća | **rok** (`due_date`) | deadline, krajnji datum |

## 4. Naslovi modala potvrde

- **Svaki** modul mora u modalu brisanja reći **na šta** se odnosi — i u tabeli
  (row akcija) i na Edit stranici (header akcija). Obrazac: naslov "Brisanje
  \<entiteta\>", a opis sadrži naziv zapisa: „Sigurno želite obrisati
  \<entitet\> "…"? Ova radnja je nepovratna.“
- Realizacija: `DeleteAction::make()->modalHeading(__('<modul>.headings.delete'))
  ->modalDescription(fn ($record) => __('<modul>.headings.delete_description',
  ['title' => $record->title]))`. Primjeri: `TaskResource`, `ReminderResource`,
  `NoteResource`.
- Ako naslovno polje može biti prazno (npr. Bilješka bez `title`), koristi
  fallback za prikaz (npr. `displayTitle()` — izvod iz sadržaja), ne prazan navod.
- Ova pravila (naslovi/opisi modala) žive uz stringove u `lang/bs/<modul>.php`
  pod ključem `headings.delete` / `headings.delete_description`.

## 5. Prazna stanja i greške

- Prazno stanje = smislen sljedeći korak, ne generički "No results".
  - Naslov: kratko stanje ("Još nema zadataka").
  - Opis: šta uraditi ("Dodajte prvi zadatak — …").
- Dosljedan pojam za istu radnju svuda (isti glagol, ista imenica).

## 6. Formati datuma i vremena

- Datum: **`d.m.Y`** (npr. `24.07.2026.`), vrijeme u **24h** formatu (`H:i`),
  bez AM/PM.
- Kontrole datuma/vremena koriste 24h picker (`->native(false)
  ->displayFormat('d.m.Y H:i')`) i, gdje ima smisla, brzu akciju „Sada“.

## 7. Pozdravi / doba dana (dashboard)

- `Dobro jutro` 05–11h, `Dobar dan` 11–18h, `Dobro veče` 18–05h (noć uključena
  u „veče“, nikad „jutro“ poslije ponoći).
- Sve doba dana, rokovi i „danas“ računaju se po **lokalnom** vremenu
  (`APP_TIMEZONE`, `Europe/Sarajevo`), ne po UTC-u. `config/app.php` mora čitati
  `env('APP_TIMEZONE')` — kad je bio hardkodovan `UTC`, pozdrav je u 06:55
  glasio „Dobro veče“ (04:55 UTC), a podsjetnici su okidali s dva sata zakašnjenja.

## 8. Šta ulazi u pretragu liste

Pretraga iznad tabele mora naći zapis po **svemu što korisnik u toj tabeli vidi
kao njegovu oznaku** — ne samo po glavnom nazivu. Konkretno, uvijek uključiti:

- naziv/naslov zapisa (i sadržaj, ako se naslov izvodi iz njega — npr. bilješka
  bez naslova prikazuje izvod iz teksta),
- **odgovornu osobu** (`assignee.user.name`), gdje entitet ima zaduženog člana,
- **oznake** (`tags.name`), gdje entitet ima oznake,
- **kategoriju** (`category.name`) i druge lookup kolone koje se prikazuju.

Relacije se pretražuju kroz `->searchable(query: fn (Builder $query, string
$search) => $query->orWhereHas('relacija', …))` na toj koloni (primjeri:
`TaskResource`, `ReminderResource`, `NoteResource`, `BillResource`).

Univerzalna pretraga (`SearchProviderContract`, Ctrl/Cmd+K) je namjerno uža —
ide samo po **vlastitom tekstu** zapisa (naslov + opis/sadržaj), jer se rezultati
svih modula miješaju u jednoj listi pa bi pogodak po imenu člana ili oznaci bio
zbunjujuć. Pretraga po odgovornoj osobi/oznakama pripada listi tog modula.
