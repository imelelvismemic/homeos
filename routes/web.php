<?php

use App\Platform\Http\HealthController;
use App\Platform\Http\HouseholdInvitationController;
use Illuminate\Support\Facades\Route;

// Filament panel (app/Providers/Filament/HomePanelProvider.php) registruje sve
// rute aplikacije na root putanji ('').
//
// Ovdje je samo JAVNA ulazna tačka pozivnice u domaćinstvo (Faza 7c): pozvana
// osoba u pravilu još nema nalog, pa ruta ne smije biti iza panel/auth sloja —
// isti razlog zbog kojeg je i link za obnovu lozinke javan.
Route::get('/pozivnica/{token}', HouseholdInvitationController::class)
    ->name('household-invitation');

// Health endpoint (Faza 8) — koristi ga deploy da potvrdi da nova verzija radi,
// i eventualni vanjski uptime monitor. Javan, ali bez detalja o infrastrukturi.
// Putanja je ENGLESKI, za razliku od korisničkih ruta: ovo je tehnički endpoint
// koji zovu skripte i monitori, i tako ga prepoznaje svako ko preuzme sistem.
Route::get('/health', HealthController::class)->name('health');
