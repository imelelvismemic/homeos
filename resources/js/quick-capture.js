/**
 * Datepicker za "Brzo dodaj" modal. Native <input type="datetime-local"> prati
 * locale browsera pa ne poštuje PRAVILA.md §6 (d.m.Y, 24h). Zato koristimo
 * flatpickr s eksplicitnim formatom. Alpine (modal) poziva window.flatpickr na
 * datetime polja.
 */
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.flatpickr = flatpickr;
