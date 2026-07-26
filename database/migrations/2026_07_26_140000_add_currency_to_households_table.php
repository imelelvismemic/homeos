<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Valuta domaćinstva (ROADMAP Faza 7c). Do sada je "KM" bio hardkodiran po
 * Finance formama i kolonama; sada je postavka domaćinstva.
 *
 * Nova domaćinstva dobijaju EUR (odluka vlasnika). POSTOJEĆA se prebacuju na BAM
 * — njihovi iznosi su unošeni kao konvertibilne marke, pa bi tihi prelazak na EUR
 * promijenio ZNAČENJE već upisanih podataka, ne samo natpis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('currency', 3)->default('EUR')->after('name');
        });

        DB::table('households')->update(['currency' => 'BAM']);
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
