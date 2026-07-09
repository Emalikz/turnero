<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TenantPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $token,
        public readonly string $tenantSlug,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña en Turnero',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-password-reset',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
