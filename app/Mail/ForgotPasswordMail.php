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

    public string $studentName;
    public string $resetToken;

    public function __construct(string $studentName, string $resetToken)
    {
        $this->studentName  = $studentName;
        $this->resetToken   = $resetToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Request - FindMe@CCIS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password',
        );
    }
}