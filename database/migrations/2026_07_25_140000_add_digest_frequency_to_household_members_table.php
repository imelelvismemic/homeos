<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            // Ritam digest emaila po članu (none/daily/weekly), opt-in (Faza 6).
            $table->string('digest_frequency')->default('none')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            $table->dropColumn('digest_frequency');
        });
    }
};
