<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_admin_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index('household_id');
        });

        Schema::create('life_admin_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('life_admin_lists')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_admin_list_items');
        Schema::dropIfExists('life_admin_lists');
    }
};
