<?php

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
