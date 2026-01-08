<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $role;
    public array $schoolIds;
    public string $signedUrl;
    public \Carbon\Carbon $expiresAt;

    /**
     * Create a new message instance.
     * Mengirim link undangan bertanda tangan.
     */
    public function __construct(string $email, string $role, array $schoolIds, string $signedUrl, \Carbon\Carbon $expiresAt)
    {
        $this->email = $email;
        $this->role = $role;
        $this->schoolIds = $schoolIds;
        $this->signedUrl = $signedUrl;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Undangan Bergabung - Supervisi Digital')
            ->view('emails.invite');
    }
}
