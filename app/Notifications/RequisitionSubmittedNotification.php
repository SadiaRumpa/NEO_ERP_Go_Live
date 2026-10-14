<?php

namespace App\Notifications;

use App\Models\StockItemRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequisitionSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public StockItemRequisition $requisition)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'requisition_submitted',
            'title'          => 'Stock Requisition Pending Approval',
            'requisition_id' => $this->requisition->id,
            'requisition_no' => $this->requisition->requisition_no,
            'message'        => "Stock requisition {$this->requisition->requisition_no} has been submitted for approval.",
            'action_url'     => route('stockitemrequisitions.show', $this->requisition->id),
        ];
    }
}
