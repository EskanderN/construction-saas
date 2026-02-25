<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Добавляем поля для раздельной работы ПТО и Снабжения
            if (!Schema::hasColumn('projects', 'pto_submitted_at')) {
                $table->timestamp('pto_submitted_at')->nullable()->after('status')->comment('Когда ПТО отправило расчеты');
            }
            if (!Schema::hasColumn('projects', 'supply_submitted_at')) {
                $table->timestamp('supply_submitted_at')->nullable()->after('pto_submitted_at')->comment('Когда снабжение отправило расчеты');
            }
            if (!Schema::hasColumn('projects', 'pto_comment')) {
                $table->text('pto_comment')->nullable()->after('supply_submitted_at')->comment('Комментарий ПТО при отправке');
            }
            if (!Schema::hasColumn('projects', 'supply_comment')) {
                $table->text('supply_comment')->nullable()->after('pto_comment')->comment('Комментарий снабжения при отправке');
            }
            if (!Schema::hasColumn('projects', 'pto_approved')) {
                $table->boolean('pto_approved')->nullable()->after('supply_comment')->comment('Утвержден ли ПТО');
            }
            if (!Schema::hasColumn('projects', 'supply_approved')) {
                $table->boolean('supply_approved')->nullable()->after('pto_approved')->comment('Утверждено ли снабжение');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'pto_submitted_at',
                'supply_submitted_at',
                'pto_comment',
                'supply_comment',
                'pto_approved',
                'supply_approved'
            ]);
        });
    }
};