<?php

namespace App\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class QueueMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct($email, $email_object)
    {
        Mail::to($email)->later(now()->addMinutes(1), $email_object);
    }
}

