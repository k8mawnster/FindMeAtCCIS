<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $itemName;
    public string $status;
    public ?string $reason;

    public function __construct(string $studentName, string $itemName, string $status, ?string $reason = null)
    {
        $this->studentName = $studentName;
        $this->itemName    = $itemName;
        $this->status      = $status;
        $this->reason      = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Post has been {$this->status} - FindMe@CCIS",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.post-status',
        );
    }
}