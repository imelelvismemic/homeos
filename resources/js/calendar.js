/**
 * Home OS kalendar (Faza 3) — self-hosted FullCalendar v6, bundlan kroz Vite.
 * Community Filament plugin (saade/filament-fullcalendar) ne podržava Laravel 13,
 * pa FullCalendar ugrađujemo direktno i hranimo ga događajima koje platforma
 * agregira iz svih CalendarSourceContract izvora (App\Platform\Calendar\CalendarService).
 *
 * Jezik ide na dva načina, i to je namjerno:
 *  - **en/de** — formatira biblioteka kroz `Intl`, jer pretraživači za te jezike
 *    imaju pune ICU podatke.
 *  - **bs** — formatiramo sami (nazivi i naslovi niže u fajlu). Pretraživači na
 *    Windowsu ne nose `bs` u ICU podacima, pa `Intl` padne na CLDR root i ispiše
 *    „2026 M07" uz engleske nazive dana. Prva verzija ove ispravke se oslonila na
 *    `Intl` za sva tri jezika i upravo je zato bila pogrešna na bosanskom.
 *
 * Terminologija dugmadi i praznih stanja NE dolazi iz bundlea nego iz
 * `lang/<jezik>/calendar.php` — bundle za bs npr. nudi „Raspored" umjesto našeg
 * „Lista" (RULES.md §3: isti termin za istu radnju svuda).
 */
import { Calendar } from '@fullcalendar/core';
import bsLocale from '@fullcalendar/core/locales/bs';
import deLocale from '@fullcalendar/core/locales/de';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

/**
 * Bosanski bundle FullCalendara NEMA `buttonHints`, `viewHint`, `navLinkHint` ni
 * ostale pomoćne tekstove (njemački ih ima, engleski su ugrađeni). Bez njih
 * biblioteka pada na engleski šablon i miješa jezike u tooltipovima i za čitače
 * ekrana: „Previous Mjesec", „This Mjesec", „Mjesec view".
 *
 * Zato ovdje dopunjavamo bosanski locale — isti sloj i isti oblik kao u samom
 * bundleu (funkcije, jer bosanski rod mijenja pridjev: „prethodni mjesec" ali
 * „prethodna sedmica"). Njemački i engleski se NE diraju; njihovi hintovi su
 * potpuni i gramatički ispravni, a naša bi ih zamjena samo pogoršala.
 */
const BS_FEMININE = ['sedmica', 'godina', 'lista'];

function bsIsFeminine(unitText) {
    return BS_FEMININE.includes(String(unitText).toLowerCase());
}

const BS_VIEW_HINTS = {
    mjesec: 'Mjesečni prikaz',
    sedmica: 'Sedmični prikaz',
    dan: 'Dnevni prikaz',
    lista: 'Prikaz liste',
};

const bsLocalePatched = {
    ...bsLocale,
    buttonHints: {
        prev(unitText) {
            return (bsIsFeminine(unitText) ? 'Prethodna ' : 'Prethodni ') + String(unitText).toLowerCase();
        },
        next(unitText) {
            return (bsIsFeminine(unitText) ? 'Sljedeća ' : 'Sljedeći ') + String(unitText).toLowerCase();
        },
        today(unitText, unit) {
            if (unit === 'day') {
                return 'Danas';
            }

            return (bsIsFeminine(unitText) ? 'Ova ' : 'Ovaj ') + String(unitText).toLowerCase();
        },
    },
    viewHint(buttonText) {
        return BS_VIEW_HINTS[String(buttonText).toLowerCase()] || ('Prikaz: ' + buttonText);
    },
    navLinkHint: 'Idi na $0',
    moreLinkHint(eventCnt) {
        return 'Prikaži još ' + eventCnt + (eventCnt === 1 ? ' događaj' : ' događaja');
    },
    closeHint: 'Zatvori',
    timeHint: 'Vrijeme',
    eventHint: 'Događaj',
};

/**
 * Njemački bundle gradi `viewHint` sufiksom po riječi („Monat" → „Monatsansicht"),
 * pa za dugme liste ispadne besmislica („Listeesansicht"). Zato samo taj jedan
 * tekst zamjenjujemo mapom; ostatak njemačkog bundlea ostaje kakav je.
 */
const DE_VIEW_HINTS = {
    monat: 'Monatsansicht',
    woche: 'Wochenansicht',
    tag: 'Tagesansicht',
    liste: 'Listenansicht',
};

const deLocalePatched = {
    ...deLocale,
    viewHint(buttonText) {
        return DE_VIEW_HINTS[String(buttonText).toLowerCase()] || deLocale.viewHint(buttonText);
    },
};

// `en` je FullCalendarov ugrađeni default, pa mu bundle ne treba.
const LOCALES = [bsLocalePatched, deLocalePatched];

/**
 * Bosanski se formatira NAŠIM kodom, ne kroz `Intl`.
 *
 * Razlog je mjerljiv: pretraživači (Edge/Chrome na Windowsu) ne nose `bs` u ICU
 * podacima, pa `Intl` pada na CLDR root i ispisuje „2026 M07" za naslov, a nazive
 * dana daje na engleskom („Mon", „Monday"). Engleski i njemački imaju pune ICU
 * podatke, pa za njih formatiranje ostaje na biblioteci — ovdje se ne dira.
 *
 * Format prati dogovor s vlasnikom: „26.juli, 2026" i „20 – 26.juli, 2026".
 */
const BS_MONTHS = [
    'januar', 'februar', 'mart', 'april', 'maj', 'juni',
    'juli', 'august', 'septembar', 'oktobar', 'novembar', 'decembar',
];
const BS_DAYS_SHORT = ['Ned', 'Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub'];
const BS_DAYS_LONG = ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota'];

// FullCalendar formaterima daje "marker" — datum čija UTC polja nose lokalno
// vrijeme, pa dan u sedmici ide kroz getUTCDay().
function bsDayOfWeek(info) {
    return info.marker.getUTCDay();
}

function bsFullDate(info) {
    return info.day + '.' + BS_MONTHS[info.month] + ', ' + info.year;
}

/**
 * Naziv mjeseca velikim slovom koristi se SAMO kad počinje naslov („Juli, 2026").
 * Unutar datuma ostaje malim („26.juli, 2026") — rečenična kapitalizacija iz
 * RULES.md §2, ista logika kao u ostatku aplikacije.
 */
function bsMonthTitle(info) {
    const month = BS_MONTHS[info.month];

    return month.charAt(0).toUpperCase() + month.slice(1) + ', ' + info.year;
}

// Raspon (sedmica, lista). `end` je već uključiv — FullCalendar oduzme 1 ms
// prije formatiranja, pa se ne smije dodatno korigovati.
function bsRange(arg) {
    const start = arg.start;
    const end = arg.end;

    if (start.year === end.year && start.month === end.month) {
        return start.day + ' – ' + end.day + '.' + BS_MONTHS[end.month] + ', ' + end.year;
    }

    if (start.year === end.year) {
        return start.day + '.' + BS_MONTHS[start.month] + ' – ' + bsFullDate(end);
    }

    return bsFullDate(start) + ' – ' + bsFullDate(end);
}

window.initHomeosCalendar = function (el, eventsUrl, options) {
    const config = options || {};
    const labels = config.labels || {};
    const locale = config.locale || 'bs';
    const isBosnian = locale === 'bs';

    // Za bs formatiramo sami; za en/de vraćamo objekte koje biblioteka razumije.
    const monthView = isBosnian
        ? {
            titleFormat: (arg) => bsMonthTitle(arg.date),
            dayHeaderFormat: (arg) => BS_DAYS_SHORT[bsDayOfWeek(arg.date)],
        }
        : { dayHeaderFormat: { weekday: 'short' } };

    const weekView = isBosnian
        ? {
            titleFormat: bsRange,
            dayHeaderFormat: (arg) => arg.date.day + ' ' + BS_DAYS_SHORT[bsDayOfWeek(arg.date)],
        }
        : { dayHeaderFormat: { weekday: 'short', day: 'numeric', omitCommas: true } };

    const dayView = isBosnian
        ? {
            titleFormat: (arg) => bsFullDate(arg.date),
            dayHeaderFormat: (arg) => BS_DAYS_LONG[bsDayOfWeek(arg.date)],
        }
        : { dayHeaderFormat: { weekday: 'long' } };

    const listView = isBosnian
        ? {
            titleFormat: bsRange,
            listDayFormat: (arg) => BS_DAYS_LONG[bsDayOfWeek(arg.date)],
            listDaySideFormat: (arg) => bsFullDate(arg.date),
        }
        : {};

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locales: LOCALES,
        locale: locale,
        // Sedmica počinje ponedjeljkom u svim jezicima: domaćinstvo je jedno, pa
        // prikaz ne smije skakati kad član promijeni jezik (bundle za `en` bi
        // inače počinjao nedjeljom).
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: {
            today: labels.today,
            month: labels.month,
            week: labels.week,
            day: labels.day,
            list: labels.list,
        },
        allDayText: labels.allDay,
        noEventsText: labels.noEvents,
        // Naslov trake i zaglavlja dana po prikazu (vidi bs formatere iznad).
        views: {
            dayGridMonth: monthView,
            timeGridWeek: weekView,
            timeGridDay: dayView,
            listWeek: listView,
        },
        // 24-satni format bez AM/PM (docs/RULES.md §6) — FullCalendar po
        // defaultu koristi engleski 12-satni prikaz u sedmici/danu/listi.
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        height: 'auto',
        nowIndicator: true,
        // JSON feed: FullCalendar sam šalje start/end prikazanog raspona, pa
        // refetchEvents() osvježava podatke bez promjene mjeseca.
        events: eventsUrl
            ? { url: eventsUrl, method: 'GET', extraParams: {}, failure: () => {} }
            : [],
        // Klik na prazan dan/termin otvara "Brzo dodaj" s već postavljenim datumom
        // ("dodajte zadatak, bilješku ili podsjetnik odakle god" iz specifikacije).
        // Kalendar ne zna koje tipove nudi — samo javi datum, registry odlučuje.
        dateClick: function (info) {
            window.dispatchEvent(new CustomEvent('homeos-quick-capture', {
                detail: { date: info.dateStr, allDay: info.allDay },
            }));
        },
        // Klik na događaj vodi na njegov izvor (npr. edit zadatka).
        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
    });

    calendar.render();

    // Brzo dodavanje javi da je nešto snimljeno → osvježi samo događaje.
    // Prikaz (mjesec/sedmica/dan i trenutni raspon) ostaje netaknut.
    window.addEventListener('homeos-quick-created', function () {
        calendar.refetchEvents();
    });

    return calendar;
};
