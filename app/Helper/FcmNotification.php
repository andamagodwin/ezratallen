<?php

namespace App\Helper;


use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class FcmNotification
{

    //Sending a Downstream Message to a Device

    public function MessageToDevice($dataObject)
    {

        // $user = auth()->user();

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(0);


        $notificationBuilder = new PayloadNotificationBuilder($dataObject->title);
        $notificationBuilder->setBody($dataObject->body)
            ->setSound('default');


        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($dataObject->data);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // $token = $user->device_token;

        $downstreamResponse = FCM::sendTo($dataObject->token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        // return "OK";
    }

    public function sendNotification($data)
    {

        //$fcm = new FcmNotification();
        if ($data->device_token) {

            $fcmObject = (object) array(
                'token' => $data->device_token,
                'body' => $data->message,
                'title' => $data->full_name,
                'data' => []
            );
            $this->MessageToDevice($fcmObject);
        }
    }
}
