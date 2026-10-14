<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplianceReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $complianceType,
        public string $assetIdentifier,
        public string $dueDate,
        public int $daysRemaining
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'compliance_reminder',
            'title'            => "Compliance Reminder: {$this->complianceType}",
            'compliance_type'  => $this->complianceType,
            'asset_identifier' => $this->assetIdentifier,
            'due_date'         => $this->dueDate,
            'days_remaining'   => $this->daysRemaining,
            'message'          => "{$this->complianceType} for {$this->assetIdentifier} is due on {$this->dueDate} ({$this->daysRemaining} days remaining).",
        ];
    }
}
