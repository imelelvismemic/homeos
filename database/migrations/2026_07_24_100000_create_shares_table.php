<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Generička sharing/privatnost tabela (DATA_MODEL.md §2). Svaki modul je koristi
// preko Shareable traita — niko ne pravi svoje is_private/visibility kolone.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->morphs('shareable'); // shareable_type + shareable_id (+ index)
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->enum('visibility', ['private', 'household', 'specific']);
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Jedan share zapis po objektu.
            $table->unique(['shareable_type', 'shareable_id']);
        });

        Schema::create('share_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_member_id')->constrained()->cascadeOnDelete();

            $table->unique(['share_id', 'household_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_recipients');
        Schema::dropIfExists('shares');
    }
};
