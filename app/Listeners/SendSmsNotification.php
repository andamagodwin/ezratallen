<?php

namespace App\Listeners;

use App\Events\SmsNotificationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Twilio\Rest\Client;

class SendSmsNotification
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
     * @param  SmsNotificationEvent  $event
     * @return void
     */
    public function handle(SmsNotificationEvent $event)
    {
        $receiverNumber = $event->phone;
        $account_sid = env('TWILIO_SID');
        $auth_token = env('TWILIO_TOKEN');
        $messaging_id = env('MESSAGING_ID');
        //$twilio_number = '+19206545562';

        


        $client = new Client($account_sid, $auth_token);
        $client->messages->create($receiverNumber, [
            "messagingServiceSid" => $messaging_id,
            'body' => $event->body,
            "from" => 'Farmsell'

        ]);
    }
}
