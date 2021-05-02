<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $link;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link,$subject)
    {
        $this->link = $link;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = env("MAIL_USERNAME", "sales@writershorizon.com");
        return $this->from($email)->subject($this->subject)->view('mails.resetpassword');
    }
}
