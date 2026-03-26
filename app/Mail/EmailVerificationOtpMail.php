<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class EmailVerificationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $otp,
        public ?string $userEmail = null
    ) {}

    public function build()
    {
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name');

        $m = $this->subject('Verify your email address')
            ->from($fromAddr, $fromName)
            ->view('emails.emailVerificationOtp')
            ->with([
                'otp'       => $this->otp,
                'userEmail' => $this->userEmail,
            ]);

        $m->withSymfonyMessage(function (SymfonyEmail $message) use ($fromAddr, $fromName) {
            if ($fromAddr) {
                $message->sender(new Address($fromAddr, $fromName ?: ''));
                $message->returnPath($fromAddr);
            }
        });

        return $m;
    }
}