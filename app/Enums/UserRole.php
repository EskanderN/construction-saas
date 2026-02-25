<?php

namespace App\Enums;

enum UserRole: string
{
    case DIRECTOR = 'director';
    case DEPUTY_DIRECTOR = 'deputy_director';
    case PTO = 'pto';
    case SUPPLY = 'supply';
    case PROJECT_MANAGER = 'project_manager';
    case SITE_MANAGER = 'site_manager';
    case ACCOUNTANT = 'accountant';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::DIRECTOR => 'Директор',
            self::DEPUTY_DIRECTOR => 'Заместитель директора',
            self::PTO => 'ПТО',
            self::SUPPLY => 'Снабжение',
            self::PROJECT_MANAGER => 'Руководитель проекта',
            self::SITE_MANAGER => 'Прораб',
            self::ACCOUNTANT => 'Бухгалтер',
        };
    }
}