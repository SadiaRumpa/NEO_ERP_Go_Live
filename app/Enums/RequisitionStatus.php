<?php

namespace App\Enums;

enum RequisitionStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case ISSUED = 'issued';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($target, [self::PENDING, self::APPROVED, self::CANCELLED]),
            self::PENDING => in_array($target, [self::APPROVED, self::REJECTED, self::CANCELLED]),
            self::APPROVED => in_array($target, [self::ISSUED, self::REJECTED, self::CANCELLED]),
            self::ISSUED => false,
            self::REJECTED => false,
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
            self::APPROVED => 'Approved',
            self::ISSUED => 'Issued',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }
}
