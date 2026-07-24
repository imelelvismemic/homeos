/**
 * Home OS kalendar (Faza 3) — self-hosted FullCalendar v6, bundlan kroz Vite.
 * Community Filament plugin (saade/filament-fullcalendar) ne podržava Laravel 13,
 * pa FullCalendar ugrađujemo direktno i hranimo ga događajima koje platforma
 * agregira iz svih CalendarSourceContract izvora (App\Platform\Calendar\CalendarService).
 *
 * Izlaže globalnu funkciju koju blade stranica poziva — namjerno bez Alpine.data,
 * da se ne miješa s Filament-ovom vlastitom Alpine instancom.
 */
import { Calendar } from '@fullcalendar/core';
import bsLocale from '@fullcalendar/core/locales/bs';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

window.initHomeosCalendar = function (el, events) {
    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: bsLocale,
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek',
        },
        buttonText: {
            today: 'Danas',
            month: 'Mjesec',
            week: 'Sedmica',
            list: 'Lista',
        },
        height: 'auto',
        nowIndicator: true,
        events: events || [],
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
