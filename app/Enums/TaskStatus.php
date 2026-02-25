<?php

namespace App\Enums;

enum TaskStatus: string
{
    case SENT = 'sent';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SENT => 'Отправлено',
            self::IN_PROGRESS => 'В работе',
            self::COMPLETED => 'Выполнено',
        };
    }
}