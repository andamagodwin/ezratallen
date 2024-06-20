<?php

namespace App\Http\Controllers\API;

use App\Helper\FcmNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatRequest;
// use App\Http\Resources\MessageResource;
// use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\User;
use App\Message;
use App\Sellers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{


    public function index()
    {
        $users = $this->getChatWithUser();

        return response()->json(['users' => $users, 'status' => true]);
    }

    public function show($id)
    {
        if ($id === auth()->user()->id) {
            return response()->json(['message' => 'Not allowed', 'status' => false], 409);
        }

        UserResource::withoutWrapping();

        $user = User::find($id);

        $seller = Sellers::where('seller_id', $id)->first();


        if ($user) {


            //update seens statement 
            $chats = $user->messages()->where('receiver_id', auth()->user()->id)->whereNull('seen_at')->get();

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
                'status' => true,
                'seller' => $seller

            ]);
        }
    }

    public function chat(ChatRequest $request)
    {

        $message = auth()->user()->messages()->create([
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'reply_id' => $request->reply_id,
        ]);
        //send device notification
        $receiver = User::find($request->receiver_id);
        if ($receiver) {
            $fcm = new FcmNotification();
            $receiver->message = $request->message;
            $fcm->sendNotification($receiver);
        }


        // broadcast(new NewMessageEvent($message->load('receiver')))->toOthers();

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function destroy($id)
    {
        $message = Message::find($id);

        $message->message_deleted_at = Carbon::now();
        $message->save();

        // broadcast(new NewMessageEvent($message->load('receiver')))->toOthers();

        return response()->json(['chats' => "Message deleted", 'status' => true]);
    }

    private function getChatWithUser()
    {
        //return  auth()->user();
        return User::query()
            ->whereHas('receiveMessages', function ($query) {
                $query->where('sender_id', auth()->user()->id);
            })
            ->orWhereHas('sendMessages', function ($query) {
                $query->where('receiver_id', auth()->user()->id);
            })
            ->withCount(['messages' => fn ($query) => $query->where('receiver_id', auth()->user()->id)->whereNull('seen_at')])
            ->with([
                'sendMessages' => function ($query) {
                    $query->whereIn('id', function ($query) {
                        $query->selectRaw('max(id)')
                            ->from('messages')
                            ->where('receiver_id', auth()->user()->id)
                            ->groupBy('sender_id');
                    });
                },
                'receiveMessages' => function ($query) {
                    $query->whereIn('id', function ($query) {
                        $query->selectRaw('max(id)')
                            ->from('messages')
                            ->where('sender_id', auth()->user()->id)
                            ->groupBy('receiver_id');
                    });
                },
            ])
            ->select('users.*', DB::raw('(
                SELECT MAX(created_at) 
                FROM messages 
                WHERE sender_id = users.id OR receiver_id = users.id
            ) as latest_created_at'))
            ->addSelect(DB::raw('(
                SELECT message
                FROM messages 
                WHERE (sender_id = users.id OR receiver_id = users.id)
                    AND created_at = latest_created_at
            ) as latest_message'))
            ->orderBy('latest_created_at', 'desc')
            //->limit(1)
            ->get();
    }

    private function loadMessages($user)
    {
        return Message::query()
            ->where(fn ($query) => $query->where('sender_id', auth()->user()->id)->where('receiver_id', $user->id))
            ->orWhere(fn ($query) => $query->where('sender_id', $user->id)->where('receiver_id', auth()->user()->id))
            ->with(['receiver', 'sender', 'reply' => fn ($query) => $query->with('sender')])
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

    public  function test_device()
    {

        $user = User::find(20);
        $fcm = new FcmNotification();

        $fcmObject = (object) array(
            'token' => $user->device_token,
            'body' => 'My record to be sent to you',
            'title' => $user->full_name,
            'data' => []
        );
        $fcm->MessageToDevice($fcmObject);

        return response()->json(['result' => true]);
    }
}
