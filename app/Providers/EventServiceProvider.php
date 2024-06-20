<?php

namespace App\Providers;

use App\Events\UserRegister;
use App\Events\MessageEvent;
use App\Events\ProductAddedEvent;
use App\Listeners\SendWelcomeNotification;
use App\Listeners\SendMessageNotification;
use App\Listeners\SendOnRegisterSmsNotification;
use App\Listeners\SendRegistrationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;

use App\Events\ProductAddEvent;
use App\Listeners\ProductAddListener;

use App\Events\ContactUsEvent;
use App\Listeners\ContactUsListenerToUser;
use App\Listeners\ContactUsListenerToFarmsell;

use App\Events\NewsLetterEvent;
use App\Listeners\NewsLetterListener;

use App\Events\SmsNotificationEvent;
use App\Listeners\SendSmsNotification;

use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        SmsNotificationEvent::class => [
            SendSmsNotification::class

        ],
        UserRegister::class => [
            SendWelcomeNotification::class,
            //SendRegistrationNotification::class,
           // SendOnRegisterSmsNotification::class

        ],

        ProductAddEvent::class => [

            ProductAddListener::class
        ],

        ContactUsEvent::class => [

            ContactUsListenerToUser::class,
            ContactUsListenerToFarmsell::class
        ],
        NewsLetterEvent::class => [
            NewsLetterListener::class

        ],
        /*
        MessageEvent::class => [
            SendMessageNotification::class,
        ],
        */
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
