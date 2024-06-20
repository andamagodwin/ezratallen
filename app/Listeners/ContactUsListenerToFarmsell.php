<?php

namespace App\Listeners;

use App\Events\ContactUsEvent;
use App\Mail\ContactUs;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ContactUsListenerToFarmsell
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
        $admin_object = new ContactUs($event->email, $event->name, $event->message);

       // Mail::to('ivanmundruku@gmail.com')->later(now()->addMinutes(1), $admin_object);

        Mail::to('sell@farmsell.org')
        ->bcc(['ivanmundruku@gmail.com'])
        ->send($admin_object);
       // ->later(now()->addMinutes(1), $admin_object);
        /*
        $admins = User::where('user_type', 'admin')->get();

        foreach ($admins as $admin) {

            Mail::to($admin->email)->later(now()->addMinutes(1), $admin_object);
        }
        */
    }
}
