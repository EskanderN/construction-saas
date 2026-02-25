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
            $table->timestamps();
            $table->boolean('pto_approved')->nullable()->after('status');
            $table->boolean('supply_approved')->nullable()->after('pto_approved');
            $table->text('pto_comment')->nullable()->after('supply_approved');
            $table->text('supply_comment')->nullable()->after('pto_comment');
            $table->timestamp('pto_submitted_at')->nullable()->after('supply_comment');
            $table->timestamp('supply_submitted_at')->nullable()->after('pto_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};