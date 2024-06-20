<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $code, $body, $subject, $name, $email;
    public function __construct($code, $body, $subject, $name, $email)
    {
        //
        $this->code = $code;
        $this->body=$body;
        $this->subject=$subject;
        $this->name=$name;
        $this->email=$email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.notification/passwordReset')
        ->subject($this->subject);
        
    }
}
