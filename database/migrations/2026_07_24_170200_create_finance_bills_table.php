<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('recurrence_rule')->nullable();
            $table->unsignedInteger('remind_days_before')->default(3);
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bills');
    }
};
