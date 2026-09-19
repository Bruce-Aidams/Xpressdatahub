<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $agentName,
        public string $token,
        public string $email,
        public string $otp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Request - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.forgot-password',
            with: [
                'agentName' => $this->agentName,
                'otp'       => $this->otp,
                'resetUrl'  => route('password.reset', [
                    'token' => $this->token,
                    'email' => $this->email,
                ]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
