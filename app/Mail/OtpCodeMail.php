<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * The verification-code email.
 *
 * Written to stay out of spam folders: a plain, personal subject; an HTML part
 * with a matching plain-text part; no images, attachments, link shorteners or
 * "salesy" words; a real Reply-To; and a unique reference header so Gmail does
 * not bundle several codes into one conversation. (Sender authentication –
 * SPF, DKIM and DMARC – is set up on the domain; see the README.)
 */
class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $purpose,
        public ?string $name,
        public int $minutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->code} is your Kids Avon verification code",
            replyTo: [new Address(Setting::get('support_email', 'avon@avoncycles.com'), 'Kids Avon Support')],
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'X-Entity-Ref-ID' => (string) Str::uuid(),
            'Auto-Submitted' => 'auto-generated',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            text: 'emails.otp-text',
            with: [
                'greeting' => $this->name ? 'Hi '.Str::of($this->name)->trim()->explode(' ')->first().',' : 'Hi there,',
                'action' => $this->purpose === 'register' ? 'finish creating your Kids Avon rider account' : 'log in to Kids Avon',
                'supportEmail' => Setting::get('support_email', 'avon@avoncycles.com'),
            ],
        );
    }
}
