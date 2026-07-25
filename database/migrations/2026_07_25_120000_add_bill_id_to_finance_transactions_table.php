<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            // Trošak nastao plaćanjem računa je povezan s tim računom (provenance +
            // idempotencija: jedan trošak po plaćenom računu). nullOnDelete: brisanje
            // računa ne briše zabilježeni trošak, samo prekida vezu.
            $table->foreignId('bill_id')
                ->nullable()
                ->after('paid_by')
                ->constrained('finance_bills')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bill_id');
        });
    }
};
