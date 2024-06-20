<?php

namespace App\Listeners;

use App\Events\MessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\MessageNotification;
use Illuminate\Support\Facades\Mail;

class SendMessageNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        //
        Mail::to($event->email)->send(new MessageNotification($event->full_name, $event->message_body));
    }
}
