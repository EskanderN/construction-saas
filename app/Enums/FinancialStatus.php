<?php

namespace App\Enums;

enum FinancialStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case NOT_PAID = 'not_paid';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING_PAYMENT => 'На оплате',
            self::PAID => 'Оплачено',
            self::NOT_PAID => 'Не оплачено',
        };
    }
}