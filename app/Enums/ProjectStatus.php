<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case CREATED = 'created';
    case IN_CALCULATION = 'in_calculation';
    case PTO_READY = 'pto_ready'; // ПТО отправил расчеты
    case SUPPLY_READY = 'supply_ready'; // Снабжение отправило расчеты
    case BOTH_READY = 'both_ready'; // Оба отправили
    case ON_APPROVAL = 'on_approval';
    case ON_REVISION = 'on_revision';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Создан',
            self::IN_CALCULATION => 'В расчете',
            self::ON_APPROVAL => 'На согласовании',
            self::ON_REVISION => 'На доработке',
            self::APPROVED => 'Утвержден',
            self::IN_PROGRESS => 'В реализации',
            self::COMPLETED => 'Завершен',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CREATED => 'gray',
            self::IN_CALCULATION => 'blue',
            self::ON_APPROVAL => 'yellow',
            self::ON_REVISION => 'orange',
            self::APPROVED => 'green',
            self::IN_PROGRESS => 'purple',
            self::COMPLETED => 'green',
        };
    }
}