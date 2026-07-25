<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profilna slika korisnika. Fajl živi na privatnom disku `documents`
 * (isti kao prilozi Life admina) — nikad u public/, jer Nginx u produkciji
 * servira host checkout, ne storage kontejnera. Prikaz ide kroz autentikovanu
 * rutu panela (App\Platform\Http\AvatarController).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }
};
