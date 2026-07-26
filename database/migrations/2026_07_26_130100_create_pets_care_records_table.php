<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Njega ljubimca: vakcina, veterinarski pregled, terapija… s datumom (`due_date`,
 * ime polja po DATA_MODEL.md §3). Datum je ono što modul "prijavi" platformi —
 * iz njega nastaju podsjetnik, stavka u kalendaru, sažetak na dashboardu i digest.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets_care_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained('pets_pets')->cascadeOnDelete();
            $table->string('type')->default('other');        // CareType enum
            $table->dateTime('due_date');
            $table->unsignedSmallInteger('remind_days_before')->default(3);
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Kratko eksplicitno ime — MySQL/MariaDB limit je 64 znaka.
            $table->index(['household_id', 'due_date'], 'pets_care_household_due');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets_care_records');
    }
};
