<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Client = 'client';
    case Performer = 'performer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Клиент',
            self::Performer => 'Исполнитель',
        };
    }
}
