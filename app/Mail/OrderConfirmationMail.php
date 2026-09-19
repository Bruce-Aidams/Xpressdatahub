<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $agentName,
        public string $network,
        public string $packageSize,
        public string $phoneNumber,
        public float  $amount,
        public string $orderId,
        public string $status,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation #' . $this->orderId . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
            with: [
                'agentName'   => $this->agentName,
                'network'     => $this->network,
                'packageSize' => $this->packageSize,
                'phoneNumber' => $this->phoneNumber,
                'amount'      => $this->amount,
                'orderId'     => $this->orderId,
                'status'      => $this->status,
                'ordersUrl'   => route('user.orders'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
