<?php

namespace App\Listeners;

use App\Events\ContactUsEvent;
use App\Mail\ContactUs;
use App\Mail\ContactUsUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ContactUsListenerToUser
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
     * @param  ContactUsEvent  $event
     * @return void
     */
    public function handle(ContactUsEvent $event)
    {
        //

        Mail::to($event->email)->send(new ContactUsUser($event->name));
    }
}
