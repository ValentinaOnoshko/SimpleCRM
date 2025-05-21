<?php
declare(strict_types=1);

namespace App\Enums;

enum DealStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW         => 'Новая',
            self::IN_PROGRESS => 'В процессе',
            self::COMPLETED   => 'Завершенная',
            self::CANCELED    => 'Отмененная',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NEW         => 'yellow',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED   => 'green',
            self::CANCELED    => 'red',
        };
    }
}
