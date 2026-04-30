<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchFoundMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $lostItemName;
    public string $foundItemName;
    public string $foundLocation;
    public string $foundDate;

    public function __construct(
        string $studentName,
        string $lostItemName,
        string $foundItemName,
        string $foundLocation,
        string $foundDate
    ) {
        $this->studentName   = $studentName;
        $this->lostItemName  = $lostItemName;
        $this->foundItemName = $foundItemName;
        $this->foundLocation = $foundLocation;
        $this->foundDate     = $foundDate;
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: 'A Possible Match Found for Your Lost Item - FindMe@CCIS',
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.match-found',
        );
    }
}