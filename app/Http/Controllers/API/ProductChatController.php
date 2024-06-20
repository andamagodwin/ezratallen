<?php

namespace App\Http\Controllers\API;

use App\Helper\ProductHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductChat\SaveChat;
use App\product;
use App\ProductChatHistory;
use App\ProductMessage;
use App\ProductMessageUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class ProductChatController extends Controller
{
    //save chat messages 


    public function saveMessage(SaveChat $request)
    {

        //call the save request

        //return "Ok";

        return $request->save();
    }



    public function deleteChat(Request $request)
    {

        /**Validate the data using validation rules
         */

        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'id' => 'required',

        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $sender_id = $request->sender_id;
        $receiver_id = $request->receiver_id;

        $chat = ProductMessage::find($request->id);



        if ($chat) {

            $array = [];

            if ($chat->delete_permission) {
                $array = json_decode($chat->delete_permission);

                array_push($array, auth()->user()->id);
            } else {
                array_push($array, auth()->user()->id);
            }

            $chat->delete_permission = json_encode($array);
            $chat->save();
        }

        $chatLatest =  ProductMessageUser::join('product_messages', 'product_message_users.message_id', '=', 'product_messages.id')
            ->where([
                ['sender_id', $sender_id],
                ['receiver_id', $receiver_id]
            ])
            ->orWhere([
                ['receiver_id', $sender_id],
                ['sender_id', $receiver_id]
            ])

            ->where('product_message_users.sender_id', $sender_id)
            ->where('product_message_users.receiver_id', $receiver_id)
            ->latest('product_message_users.id')->first();



        $history = ProductChatHistory::where('receiver_id', $receiver_id)
            ->where('sender_id', $sender_id)->first();
        if ($chatLatest && $history && $chat) {
            $history->sender_id = $sender_id;
            $history->receiver_id = $receiver_id;
            $history->message = $chatLatest->content;
            $history->delete_permission = json_encode($array);
            $history->save();
        }

        return response()->json(['result' => true]);
    }

    // get chat messages



    public function getChatMessage(Request $request)
    {


        // $sender_id = 1;
        $sender_id = auth()->user()->id;

        $receiver_id = $request->user_id;

        $product_id = $request->product_id;

        //get the product details

        $product = product::find($product_id);


        $receiver = User::find($receiver_id);

        //$sender = User::find($request->sender_id);



        $checkOnline = new ProductHelper();
        $online = $checkOnline->onLineStatus($receiver);

        //get logged in user records 
        $messages = ProductMessageUser::join('product_messages', 'product_message_users.message_id', '=', 'product_messages.id')
            ->where([
                ['sender_id', $sender_id],
                ['receiver_id', $receiver_id]
            ])
            ->orWhere([
                ['receiver_id', $sender_id],
                ['sender_id', $receiver_id]
            ])

            ->where('product_messages.product_id', $product_id)
            ->orderBy('product_messages.id', 'Asc')

            ->paginate(30);

        //get the user in each case 
        $check = "unit";
        $initial = "unit";


        foreach ($messages as $key => $c) {

            if ($c->receiver_id === $sender_id) {
                $message = ProductMessage::find($c->message_id);
                $message->status = '1';
                $message->save();
            }

            $created = new Carbon($c->created_at);
            $now = Carbon::now();

            $c->time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');
            //$c->time_zone = auth()->user()->timezone;




            $time = $created->diff($now)->days;


            if ($check === $initial) {

                $todayCheck = Carbon::now()->isSameDay($c->created_at);

                if ($todayCheck) {
                    //attache todays day
                    $c->date_group = 'Today';
                } else {
                    $c->date_group = Carbon::parse($c->created_at)->format('d M Y');
                }
            }

            if ($time === $check) {
                //$c->date_group = false;
            }

            //check if the dates  are the same, if not then attach another date match

            if (($check !== "unit") && ($check !== $time)) {

                $c->date_group = Carbon::parse($c->created_at)->format('d M Y');
            }






            if ($c->sender_id == $sender_id) {
                $user = User::find($c->receiver_id);
            } else {
                $user = User::find($c->sender_id);
            }
            $check = $time;
            $c->user = $user;






            //if ($check_delete)


        }


        //get two three products of the user 
        $products = product::where('user_id', $product ? $product->user_id : 'null')->paginate(3);

        //unset($messages[0]);

        return response()->json([
            'messages' => $messages,
            'products' => $products,
            'online' => $online
        ]);
    }


    // get chat messages version 2



    public function getChatMessageVersion2(Request $request)
    {


        // $sender_id = 1;
        $sender_id = auth()->user()->id;

        $receiver_id = $request->user_id;

        $product_id = $request->product_id;

        //get the product details

        $product = product::find($product_id);


        $receiver = User::find($receiver_id);

        //$sender = User::find($request->sender_id);

        $messageBody = [];


        $checkOnline = new ProductHelper();
        $online = $checkOnline->onLineStatus($receiver);

        //get logged in user records 
        $messages = ProductMessageUser::join('product_messages', 'product_message_users.message_id', '=', 'product_messages.id')
            ->where([
                ['sender_id', $sender_id],
                ['receiver_id', $receiver_id]
            ])
            ->orWhere([
                ['receiver_id', $sender_id],
                ['sender_id', $receiver_id]
            ])

            ->where('product_messages.product_id', $product_id)
            ->orderBy('product_messages.id', 'Asc')

            ->get();

        //get the user in each case 
        $check = "unit";
        $initial = "unit";


        foreach ($messages as $key => $c) {

            if ($c->receiver_id === $sender_id) {
                $message = ProductMessage::find($c->message_id);
                $message->status = '1';
                $message->save();
            }

            $created = new Carbon($c->created_at);
            $now = Carbon::now();

            $c->time = Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');
            //$c->time_zone = auth()->user()->timezone;




            $time = $created->diff($now)->days;


            if ($check === $initial) {

                $todayCheck = Carbon::now()->isSameDay($c->created_at);

                if ($todayCheck) {
                    //attache todays day
                    $c->date_group = 'Today';
                } else {
                    $c->date_group = Carbon::parse($c->created_at)->format('d M Y');
                }
            }

            if ($time === $check) {
                //$c->date_group = false;
            }

            //check if the dates  are the same, if not then attach another date match

            if (($check !== "unit") && ($check !== $time)) {

                $c->date_group = Carbon::parse($c->created_at)->format('d M Y');
            }






            if ($c->sender_id == $sender_id) {
                $user = User::find($c->receiver_id);
            } else {
                $user = User::find($c->sender_id);
            }
            $check = $time;
            $c->user = $user;


            $check_delete = false;;

            if ($c->delete_permission) {


                $array = json_decode($c->delete_permission);
                $check_delete = in_array(auth()->user()->id, $array);

                if ($check_delete) {
                    //unset($messages[$key]);
                } else {
                    array_push($messageBody, $c);
                }
            } else {
                array_push($messageBody, $c);
            }



            //if ($check_delete)


        }


        //get two three products of the user 
        $products = product::where('user_id', $product ? $product->user_id : 'null')->paginate(3);



        return response()->json([
            'messages' => $messageBody,
            'products' => $products,
            'online' => $online
        ]);
    }



    public function getChatList(Request $request)
    {


        $user_id = auth()->user()->id;
        //$user_id = 1;



        //get logged in user records 
        $chatList = ProductChatHistory::where('receiver_id', $user_id)
            ->orWhere('sender_id', $user_id)
            ->paginate(30);

        $prod_users = ProductMessageUser::join('product_messages', 'product_message_users.message_id', '=', 'product_messages.id')

            ->where('product_message_users.receiver_id', $user_id)

            ->where('product_messages.status', '<>', '1')

            ->get();

        foreach ($prod_users as $c) {
            $check = ProductMessage::find($c->message_id);
            if ($check && $check->status == 0) {
                $check->status = '2';
                $check->save();
            }
        }

        foreach ($chatList as $c) {
            //get the user and the product

            $c->count = ProductMessageUser::join('product_messages', 'product_message_users.message_id', '=', 'product_messages.id')
                ->where('product_message_users.receiver_id', $user_id)
                ->where('product_messages.product_id', $c->product_id)
                ->where('product_messages.status',  '<>', '1')
                ->count();


            $todayCheck = Carbon::now()->isSameDay($c->created_at);
            if ($todayCheck) {
                $date =  Carbon::createFromFormat('Y-m-d H:i:s', $c->created_at)->setTimezone($request->zone)->isoFormat('h:mm a');
                $c->date =   $date;
            } else {

                $yearCheck = Carbon::now()->isSameYear($c->created_at);
                if ($yearCheck) {
                    $date = Carbon::parse($c->created_at)->format('M d');
                } else {
                    $date = Carbon::parse($c->created_at)->format('M d, Y');
                }
                $c->date =   $date;
            }

            if ($c->sender_id == $user_id) {
                $user = User::find($c->receiver_id);
            } else {
                $user = User::find($c->sender_id);
            }
            $c->data_user = $user;
            $prod = product::find($c->product_id);
            $c->product = $prod;
            $user = User::find($c->sender_id);

            if ($prod) {
                $c->owner = User::find($prod->user_id);
            }
        }




        return response()->json(['list' => $chatList, 'count' => count($prod_users)]);
    }
}
