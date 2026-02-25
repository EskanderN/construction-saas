<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('supply_user_id')->constrained('users');
            $table->foreignId('site_manager_user_id')->nullable()->constrained('users');
            $table->string('material_name');
            $table->integer('quantity');
            $table->string('unit');
            $table->date('delivery_date')->nullable();
            $table->date('confirmed_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_deliveries');
    }
};