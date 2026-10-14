<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case PENDING = 'pending';
    case INPROGRESS = 'inprogress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::PENDING => in_array($target, [self::INPROGRESS, self::COMPLETED, self::CANCELLED]),
            self::INPROGRESS => in_array($target, [self::COMPLETED, self::CANCELLED]),
            self::COMPLETED => false,
            self::CANCELLED => in_array($target, [self::PENDING]),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::INPROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
