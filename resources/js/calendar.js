/**
 * Home OS kalendar (Faza 3) — self-hosted FullCalendar v6, bundlan kroz Vite.
 * Community Filament plugin (saade/filament-fullcalendar) ne podržava Laravel 13,
 * pa FullCalendar ugrađujemo direktno i hranimo ga događajima koje platforma
 * agregira iz svih CalendarSourceContract izvora (App\Platform\Calendar\CalendarService).
 *
 * Nazive mjeseci/dana i naslov ispisujemo EKSPLICITNO na bosanskom (dayHeaderContent
 * + datesSet), ne oslanjajući se na Intl/locale — pokazalo se nepouzdano u praksi.
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

const MONTHS = [
    'januar', 'februar', 'mart', 'april', 'maj', 'juni',
    'juli', 'august', 'septembar', 'oktobar', 'novembar', 'decembar',
];
const DAYS_SHORT = ['ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub'];
const DAYS_LONG = ['nedjelja', 'ponedjeljak', 'utorak', 'srijeda', 'četvrtak', 'petak', 'subota'];

function bsTitle(view) {
    const start = view.currentStart;

    if (view.type === 'dayGridMonth') {
        return MONTHS[start.getMonth()] + ' ' + start.getFullYear() + '.';
    }

    if (view.type === 'timeGridDay') {
        return DAYS_LONG[start.getDay()] + ', ' + start.getDate() + '. ' + MONTHS[start.getMonth()] + ' ' + start.getFullYear() + '.';
    }

    // Sedmica / lista — raspon (currentEnd je ekskluzivan).
    const end = new Date(view.currentEnd.getTime() - 86400000);
    const startStr = start.getDate() + '. ' + MONTHS[start.getMonth()];
    const endStr = end.getDate() + '. ' + MONTHS[end.getMonth()] + ' ' + end.getFullYear() + '.';

    return startStr + ' – ' + endStr;
}

window.initHomeosCalendar = function (el, events) {
    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: {
            today: 'Danas',
            month: 'Mjesec',
            week: 'Sedmica',
            day: 'Dan',
            list: 'Lista',
        },
        allDayText: 'Cijeli dan',
        noEventsText: 'Nema događaja za prikaz',
        height: 'auto',
        nowIndicator: true,
        events: events || [],
        // Zaglavlja dana na bosanskom.
        dayHeaderContent: function (arg) {
            if (arg.view.type === 'dayGridMonth') {
                return DAYS_SHORT[arg.date.getDay()];
            }

            return DAYS_SHORT[arg.date.getDay()] + ' ' + arg.date.getDate() + '.';
        },
        // Naslov trake na bosanskom (prepiši nakon svake promjene raspona).
        datesSet: function (arg) {
            const title = el.querySelector('.fc-toolbar-title');
            if (title) {
                title.textContent = bsTitle(arg.view);
            }
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

    return calendar;
};
