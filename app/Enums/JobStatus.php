<?php

namespace App\Enums;

enum JobStatus: string
{
    case DRAFT = 'draft';
    case NEW = 'new';
    case ACCEPTED = 'accepted';
    case INPROGRESS = 'inprogress';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($target, [self::NEW, self::ACCEPTED, self::INPROGRESS, self::REJECTED, self::ARCHIVED]),
            self::NEW => in_array($target, [self::ACCEPTED, self::INPROGRESS, self::ON_HOLD, self::REJECTED, self::ARCHIVED]),
            self::ACCEPTED => in_array($target, [self::INPROGRESS, self::ON_HOLD, self::REJECTED, self::ARCHIVED]),
            self::INPROGRESS => in_array($target, [self::ON_HOLD, self::COMPLETED, self::REJECTED, self::ARCHIVED]),
            self::ON_HOLD => in_array($target, [self::INPROGRESS, self::REJECTED, self::ARCHIVED]),
            self::COMPLETED => in_array($target, [self::ARCHIVED]),
            self::REJECTED => in_array($target, [self::ARCHIVED]),
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
            self::DRAFT => 'Draft',
            self::NEW => 'New',
            self::ACCEPTED => 'Accepted',
            self::INPROGRESS => 'In Progress',
            self::ON_HOLD => 'On Hold',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
            self::ARCHIVED => 'Archived',
        };
    }
}
