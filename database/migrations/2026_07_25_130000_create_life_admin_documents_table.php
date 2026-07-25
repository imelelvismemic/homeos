<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_admin_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('type');                        // id_document | warranty | renewal | contract | other
            $table->string('title');
            $table->date('expiry_date')->nullable();       // istek/obnova (ime po DATA_MODEL §3)
            $table->unsignedInteger('remind_days_before')->default(30);
            $table->string('file_path')->nullable();       // privatni disk 'documents'
            $table->string('file_name')->nullable();       // originalni naziv za download
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_admin_documents');
    }
};
