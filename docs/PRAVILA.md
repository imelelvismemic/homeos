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
- **Filament sam title-case-uje naslove izvedene iz labela** ("Kućni Ljubimci",
  "Liste Za Kupovinu"). To se NE krpi override-om `getTitle()` po stranici —
  tako se greška vraćala sa svakim novim modulom. Rješenje je jednom, u osnovi:
  svaki Resource modula nasljeđuje
  `App\Platform\Filament\Resources\ModuleResource`, koja gasi Filamentov
  `$hasTitleCaseModelLabel`. Novi modul time dobija ispravnu kapitalizaciju bez
  ijedne dodatne linije (checklist u `CLAUDE.md` §14).

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

Izuzetak koji to potvrđuje: **članovi domaćinstva** su i sami rezultat pretrage
(grupa „Članovi domaćinstva“, samo trenutno odabrano domaćinstvo). Ime člana je
tu njegov *vlastiti* tekst — ali i dalje ne izvlači zadatke/račune na kojima je
taj član odgovorna osoba.

## 9. Kretanje kroz forme („Nazad" uvijek vodi na listu)

- Nakon **dodavanja** zapisa korisnik ostaje na formi **uređivanja** tog zapisa
  (vidi da je snimljeno i može odmah dopuniti detalje).
- Na formi uređivanja dugme **„Nazad" vodi na listu** tog modula — nikad natrag
  na formu dodavanja. Filament po defaultu radi `window.history.back()`, pa
  korisnik koji je upravo snimio zapis završi na praznoj formi dodavanja i
  djeluje kao da ništa nije snimljeno.
- Realizacija: svaka `EditRecord` stranica koristi
  `App\Platform\Filament\Concerns\CancelReturnsToList` (uklanja `history.back()`
  i postavlja URL liste). Dio je checkliste za novi modul (`CLAUDE.md` §14).

## 10. Iznosi i valuta

- **Valuta se nikad ne piše u kod.** Do Faze 7c je „KM“ bio hardkodiran po
  Finance formama i kolonama; sada je postavka domaćinstva
  (`households.currency`, podrazumijevano `EUR`, bira se u Postavkama domaćinstva;
  postojeća domaćinstva su migracijom prebačena na `BAM`, jer su njihovi iznosi
  unošeni kao marke — tihi prelazak na EUR bi promijenio značenje podataka).
- Svaki ispis iznosa ide kroz `App\Platform\Support\Currency`:
  `Currency::format($iznos)` → `1.234,56` u obliku `1,234.56 €`,
  `Currency::symbol()` za prefiks polja u formi. Finance modul ima tanki omotač
  `Money::format()` koji samo delegira — ne pravi vlastito formatiranje.
- Format je namjerno isti za sve valute (`iznos` pa simbol), a ne Intl po lokalu
  („BAM 700.00“) — tabele i widgeti tako ostaju poravnati i čitljivi.
- **Novi modul s iznosima** deklariše `'uses_currency' => true` u
  `config/homeos-apps.php`. Time se polje za izbor valute pojavi u postavkama
  domaćinstva čim je taj modul uključen — platforma ne poznaje module po imenu.
- Iznosi u bazi su uvijek `decimal(12,2)`, bez valute u koloni: valuta je
  svojstvo domaćinstva, ne pojedinačnog zapisa.

## 11. Obavještenja se sastavljaju i IZVAN web zahtjeva

Podsjetnici, sažeci i upozorenja nastaju u **konzoli** (scheduler, artisan
komande), gdje Filament nema „trenutno domaćinstvo" ni prijavljenog korisnika.
Zato u Notification klasama:

- **Tenant se prosljeđuje eksplicitno**:
  `Resource::getUrl('edit', ['record' => $model, 'tenant' => $model->household_id])`.
  Bez toga `getUrl()` puca na nedostajućem `{tenant}` parametru i **cijeli email
  padne**, dok in-app obavijest ipak stigne (database kanal se izvršava prvi) —
  simptom je „obavijest vidim, email ne dobijam".
- Ne oslanjati se na `auth()->user()` ni `Filament::getTenant()`; sve što
  notifikaciji treba nosi model ili konstruktor.

**Zašto testovi ovo ne uhvate sami:** `Notification::fake()` **nikad ne poziva
`toMail()`** — provjerava samo da je obavještenje poslano. Zato uz svaki
`toMail()` koji gradi URL ide i test koji ga **stvarno sastavi** bez tenanta:

```php
Filament::setTenant(null);                       // scheduler kontekst
expect((new ReminderDue($r))->toMail($member)->actionUrl)->toContain((string) $r->household_id);
```

Isti obrazac vrijedi za svaku zamjenu produkcijskog sloja u testu (vidi i vezu
`DatabaseDumper` iz Faze 8): ako test veže lažnu implementaciju, mora postojati i
test koji provjerava **pravu**.
