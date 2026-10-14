<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case INPROGRESS = 'inprogress';
    case ON_HOLD = 'on_hold';
    case CONVERTED_TO_JOB = 'converted_to_job';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::NEW => in_array($target, [self::INPROGRESS, self::ON_HOLD, self::REJECTED, self::ARCHIVED, self::CONVERTED_TO_JOB]),
            self::INPROGRESS => in_array($target, [self::ON_HOLD, self::CONVERTED_TO_JOB, self::REJECTED, self::ARCHIVED]),
            self::ON_HOLD => in_array($target, [self::INPROGRESS, self::REJECTED, self::ARCHIVED]),
            self::CONVERTED_TO_JOB => in_array($target, [self::ARCHIVED]),
            self::REJECTED => in_array($target, [self::INPROGRESS, self::ARCHIVED]),
            self::ARCHIVED => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::INPROGRESS => 'In Progress',
            self::ON_HOLD => 'On Hold',
            self::CONVERTED_TO_JOB => 'Converted To Job',
            self::REJECTED => 'Rejected',
            self::ARCHIVED => 'Archived',
        };
    }
}
