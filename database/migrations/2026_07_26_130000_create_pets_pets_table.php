<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kućni ljubimci (ROADMAP Faza 7b, DATA_MODEL.md §4d). Prefiks tabele po modulu,
 * `household_id` od prve migracije (CLAUDE.md §15).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('species')->default('other');     // PetSpecies enum
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'name'], 'pets_pets_household_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets_pets');
    }
};
