<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->text('content');
            $table->string('section')->nullable(); // pto, supply, general
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};