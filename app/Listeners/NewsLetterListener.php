<?php

namespace App\Listeners;

use App\Events\NewsLetterEvent;
use App\Mail\NewsLetter;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NewsLetterListener
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
     * @param  NewsLetterEvent  $event
     * @return void
     */
    public function handle(NewsLetterEvent $event)
    {
    

        Mail::to($event->email)->send(new NewsLetter($event->name));
    }
}
