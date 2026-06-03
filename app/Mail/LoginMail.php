<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $password;
    public string $email;

    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Kamu Telah Dibuat',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
