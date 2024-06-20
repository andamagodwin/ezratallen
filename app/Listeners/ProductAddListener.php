<?php

namespace App\Listeners;

use App\Events\ProductAddEvent;
use App\Mail\ProductAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ProductAddListener
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
     * @param  ProductAddEvent  $event
     * @return void
     */
    public function handle(ProductAddEvent $event)
    {
        Mail::to($event->email)->send(new ProductAdded($event->full_name, $event->body, $event->product));
    }
}
