<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Po članu domaćinstva: koje kategorije obavještenja želi primati emailom /
// u digestu (DATA_MODEL.md §1). In-app (database) kanal se uvijek šalje;
// email je opcionalan po kategoriji (Faza 6 UI za ovo).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_member_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('digest_enabled')->default(false);
            $table->timestamps();

            $table->unique(['household_member_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
