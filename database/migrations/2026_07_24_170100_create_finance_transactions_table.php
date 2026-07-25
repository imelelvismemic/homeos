<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->string('type');                        // income | expense
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->foreignId('paid_by')->nullable()->constrained('household_members')->nullOnDelete();
            $table->timestamps();

            $table->index(['household_id', 'date']);
        });

        Schema::create('finance_transaction_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('finance_transactions')->cascadeOnDelete();
            $table->foreignId('household_member_id')->constrained('household_members')->cascadeOnDelete();
            $table->timestamps();

            // Eksplicitno kratko ime — auto-generisano (73 zn.) prelazi MySQL limit od 64.
            $table->unique(['transaction_id', 'household_member_id'], 'fin_tx_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transaction_participants');
        Schema::dropIfExists('finance_transactions');
    }
};
