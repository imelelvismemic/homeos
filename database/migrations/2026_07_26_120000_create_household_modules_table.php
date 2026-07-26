<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uključenost modula PO DOMAĆINSTVU (ROADMAP Faza 7, CLAUDE.md §12).
 *
 * Čuvaju se samo ODSTUPANJA od podrazumijevane vrijednosti iz
 * `config/homeos-apps.php` — domaćinstvo koje ništa nije diralo nema nijedan
 * red ovdje i dobija default. Tako novi modul automatski postaje dostupan svima,
 * bez popunjavanja tabele unazad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');                   // ključ iz config/homeos-apps.php
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            // Kratko ime indeksa — MySQL/MariaDB limit je 64 znaka.
            $table->unique(['household_id', 'module_key'], 'household_modules_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_modules');
    }
};
