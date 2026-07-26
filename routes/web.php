<?php

use App\Platform\Http\HealthController;
use App\Platform\Http\HouseholdInvitationController;
use App\Platform\Http\LocaleController;
use Illuminate\Support\Facades\Route;

// Filament panel (app/Providers/Filament/HomePanelProvider.php) registruje sve
// rute aplikacije na root putanji ('').
//
// Ovdje je samo JAVNA ulazna tačka pozivnice u domaćinstvo (Faza 7c): pozvana
// osoba u pravilu još nema nalog, pa ruta ne smije biti iza panel/auth sloja —
// isti razlog zbog kojeg je i link za obnovu lozinke javan.
//
// Throttle (sigurnosni pregled, Faza 9c): sve tri rute su javne. Prijava,
// registracija i obnova lozinke već imaju Filamentov `rateLimit(5)`, a ove nisu
// imale nikakvo ograničenje — token pozivnice je 48 slučajnih znakova (hashiran
// u bazi), pa pogađanje nije realna prijetnja, ali ograničenje je jeftino i
// sprječava da neko javnom rutom troši resurse servera.
Route::get('/invitation/{token}', HouseholdInvitationController::class)
    ->middleware('throttle:20,1')
    ->name('household-invitation');

// Health endpoint (Faza 8) — koristi ga deploy da potvrdi da nova verzija radi,
// i eventualni vanjski uptime monitor. Javan, ali bez detalja o infrastrukturi.
//
// JEDINA javna ruta BEZ `throttle` (RULES.md §12), i to namjerno: Laravelov
// rate limiter čuva brojače u cacheu, pa bi na pokvarenom cacheu middleware
// pukao PRIJE kontrolera i endpoint bi vratio 500. A upravo tada mora raditi —
// njegov posao je da prijavi `cache: false`, ne da i sam padne s infrastrukturom
// koju provjerava. Uhvaćeno testom u Fazi 9c (HealthEndpointTest zamjenjuje
// cache store, pa je throttle odmah pukao). Odgovor je jeftin i bez upisa, a
// pred njim stoji Apache/Virtualmin.
Route::get('/health', HealthController::class)->name('health');

// Promjena jezika (Faza 9b). Van panela jer prekidač stoji i na prijavi, gdje
// tenant još ne postoji. POST, jer mijenja stanje (sesija + users.locale).
Route::post('/language/{locale}', LocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale');
