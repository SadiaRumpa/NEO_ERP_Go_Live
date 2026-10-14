<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($target, [self::PENDING, self::CANCELLED]),
            self::PENDING => in_array($target, [self::ACCEPTED, self::REJECTED, self::CANCELLED, self::DRAFT]),
            self::ACCEPTED => false, // accepted quotations are locked
            self::REJECTED => in_array($target, [self::PENDING, self::CANCELLED]),
            self::CANCELLED => false,
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
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }
}
