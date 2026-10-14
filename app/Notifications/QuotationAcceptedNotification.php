<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public Quotation $quotation)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'quotation_accepted',
            'title'          => 'Quotation Accepted by Customer',
            'quotation_id'   => $this->quotation->id,
            'customer_name'  => $this->quotation->customer?->company_name ?? 'Customer',
            'signer_name'    => $this->quotation->signer_name,
            'signed_at'      => $this->quotation->signed_at,
            'total_amount'   => $this->quotation->total_amount,
            'message'        => "Quotation #{$this->quotation->id} was accepted and signed by {$this->quotation->signer_name}.",
            'action_url'     => route('quotations.show', $this->quotation->id),
        ];
    }
}
