<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ORIGINAL_SPEC: podsjetnici su "namijenjeni određenim članovima" i mogu se
 * "dodjeljivati članovima" (uvid ko je odgovoran) — dodajemo odgovornu osobu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminders_reminders', function (Blueprint $table) {
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('created_by')
                ->constrained('household_members')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reminders_reminders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
