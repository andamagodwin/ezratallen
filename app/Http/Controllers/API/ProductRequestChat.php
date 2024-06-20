<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductRequestChat as AppProductRequestChat;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ProductRequestChat extends Controller
{

    public function chat(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_request_id' => 'required',
            'message' => 'required',
            'receiver_id' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }

        $message = auth()->user()->productRequestChats()->create([
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'product_request_id' => $request->product_request_id
        ]);

        // broadcast(new NewMessageEvent($message->load('receiver')))->toOthers();

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function show(Request $request)
    {

        /**Validate the data using validation rules
         */
        $validator = Validator::make($request->all(), [
            'product_request_id' => 'required',
            'user_id' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            /**Return error message
             */
            return response()->json(['error' => $validator->errors()]);
        }
        $id = $request->user_id;

        if ($id === auth()->user()->id) {
            return response()->json(['message' => 'Not allowed', 'status' => false], 409);
        }


        $product_request_id = $request->product_request_id;
        $user = User::find($id);

        if ($user) {


            //update seens statement 
            $chats = $user->productRequestChats()->where('receiver_id', auth()->user()->id)
                ->where('product_request_id', $product_request_id)
                ->whereNull('seen_at')->get();

            $chats->each(function ($chat) {
                $chat->seen_at = now();
                $chat->save();
            });

            // if ($result->count()) {
            //     $message = $result->last()->load('sender');
            //     broadcast(new ReadMessageEvent($message))->toOthers();
            // }
            return response()->json([
                //'chats' => $chats,
                'messages' => $this->loadMessages($user),
                'status' => true
            ]);
        }
    }

    public function destroy($id)
    {
        $message = AppProductRequestChat::find($id);
        $message->message_deleted_at = Carbon::now();
        $message->save();

        // broadcast(new NewMessageEvent($message->load('receiver')))->toOthers();

        return response()->json(['chats' => "Message deleted", 'status' => true]);
    }

    private function loadMessages($user)
    {
        return AppProductRequestChat::query()
            ->where(fn ($query) => $query->where('sender_id', auth()->user()->id)->where('receiver_id', $user->id))
            ->orWhere(fn ($query) => $query->where('sender_id', $user->id)->where('receiver_id', auth()->user()->id))
            ->with(['receiver', 'sender'])
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($message) {
                return $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' :
                    $message->created_at->format('F j, Y'));
            })
            ->map(function ($messages, $date) {
                return [
                    'date' => $date,
                    'messages' => $messages //MessageResource::collection($messages),
                ];
            })
            ->values()
            ->toArray();
    }
}
