<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone')->default('Europe/Sarajevo')->after('password');
            $table->string('locale')->default('bs')->after('timezone');
            $table->foreignId('current_household_id')->nullable()->after('locale')
                ->constrained('households')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_household_id');
            $table->dropColumn(['timezone', 'locale']);
        });
    }
};
