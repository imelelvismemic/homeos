<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pozivnica putem linka (ROADMAP Faza 7c, DATA_MODEL.md §1).
 *
 * Rješava rupu iz ranijeg toka: vlasnik je mogao pozvati samo VEĆ registrovanog
 * korisnika, pa je pozvana osoba morala prvo sama otvoriti nalog — a registracija
 * je obavezno vodi na kreiranje vlastitog domaćinstva. Tako je završavala s
 * praznim domaćinstvom koje joj ne treba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('member');
            // Čuva se HASH tokena — baza ne smije sadržavati ključ kojim se ulazi
            // u domaćinstvo (isti princip kao lozinke i reset tokeni).
            $table->string('token', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('accepted_at')->nullable();
            $table->timestamps();

            // Kratko eksplicitno ime — MySQL/MariaDB limit je 64 znaka.
            $table->unique(['household_id', 'email'], 'household_invites_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_invitations');
    }
};
