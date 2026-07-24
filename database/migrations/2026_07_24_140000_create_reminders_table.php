<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();       // kad podsjetnik okine
            $table->dateTime('completed_at')->nullable();     // postavljeno kad okine/gotovo
            $table->string('recurrence_rule')->nullable();
            $table->nullableMorphs('remindable');             // opciona veza na bilo koji entitet
            $table->timestamps();

            $table->index(['household_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders_reminders');
    }
};
