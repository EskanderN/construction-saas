<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('created');
            $table->foreignId('created_by')->constrained('users');
            
            // Новые поля для раздельной работы ПТО и Снабжения
            // УБИРАЕМ "after" - в CREATE TABLE это не нужно
            $table->boolean('pto_approved')->nullable();
            $table->boolean('supply_approved')->nullable();
            $table->text('pto_comment')->nullable();
            $table->text('supply_comment')->nullable();
            $table->timestamp('pto_submitted_at')->nullable();
            $table->timestamp('supply_submitted_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};