<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case NEW = 'new';
    case INPROGRESS = 'inprogress';
    case CONVERTED_TO_LEAD = 'converted_to_lead';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::NEW => in_array($target, [self::INPROGRESS, self::REJECTED, self::ARCHIVED, self::CONVERTED_TO_LEAD]),
            self::INPROGRESS => in_array($target, [self::CONVERTED_TO_LEAD, self::REJECTED, self::ARCHIVED]),
            self::CONVERTED_TO_LEAD => in_array($target, [self::ARCHIVED]),
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
            self::CONVERTED_TO_LEAD => 'Converted To Lead',
            self::REJECTED => 'Rejected',
            self::ARCHIVED => 'Archived',
        };
    }
}
