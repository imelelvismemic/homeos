<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->longText('body');                    // HTML iz RichEditor-a
            $table->date('journal_date')->nullable();     // unos dnevnika
            $table->nullableMorphs('notable');            // opciona veza na bilo koji entitet
            $table->timestamps();

            $table->index(['household_id', 'journal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes_notes');
    }
};
