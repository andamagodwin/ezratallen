<?php

namespace App\Http\Requests\ProductChat;

use App\Helper\FcmNotification;
use App\ProductChatHistory;
use App\ProductMessage;
use App\ProductMessageUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SaveChat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            // 'sender_id' => 'required',
            'receiver_id' => 'required',
            'product_id' => 'required',
            'txt' => 'required'

        ];
    }


    public function save()
    {

        $user = auth()->user();

        //get sender id

        $sender_id = auth()->user()->id;
        //$sender_id = 2;

        //save the message sent

        $message =   ProductMessage::create([
            'content' => $this->txt,
            'product_id' => $this->product_id,
        ]);


        $receiver = User::find($this->receiver_id);

        //create the records for the sender and receiver 
        if ($message) {
            ProductMessageUser::create([
                'message_id' => $message->id,
                'sender_id' => $sender_id,
                'receiver_id' => $this->receiver_id
            ]);
        }
        $last_history_check = null;

        $last_history_check1 = ProductChatHistory::where('receiver_id', $this->receiver_id)
            ->where('sender_id', $sender_id)
            ->first();
        if ($last_history_check1 && $last_history_check1->product_id == $this->product_id) {
            $last_history_check = $last_history_check1;
        }

        $last_history_check2 = ProductChatHistory::where('receiver_id', $sender_id)
            ->where('sender_id',  $this->receiver_id)
            ->first();

        if ($last_history_check2 && $last_history_check2->product_id == $this->product_id) {
            $last_history_check = $last_history_check2;
        }

        if ($last_history_check) {
            $last_history_check->sender_id = $sender_id;
            $last_history_check->receiver_id = $this->receiver_id;
            $last_history_check->message = $this->txt;
            $last_history_check->product_id = $this->product_id;
            $last_history_check->read_status = 0;
            $last_history_check->created_at = Carbon::now();
            $last_history_check->last_send_id = $sender_id;
            $last_history_check->save();
        } else {
            $last_history_check = new ProductChatHistory();
            $last_history_check->sender_id = $sender_id;
            $last_history_check->receiver_id = $this->receiver_id;
            $last_history_check->message = $this->txt;
            $last_history_check->product_id = $this->product_id;
            $last_history_check->read_status = 0;
            $last_history_check->last_send_id = $sender_id;
            $last_history_check->save();
        }


        if ($receiver) {
            $fcm = new FcmNotification();
            $fcmObject = (object) array(
                'token' => $receiver->device_token,
                'body' => $this->txt,
                'title' => $user->full_name,
                'data' => []
            );
            $fcm->MessageToDevice($fcmObject);
        }





        return response()->json(['result' => true]);
    }
}
