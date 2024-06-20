<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\Helper;

class UserResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            //'uuid' => $this->uuid,
            //'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'last_seen_at' => Helper::userLastActivityStatus($this->last_seen_at),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'messages_count' => $this->whenCounted('messages', $this->messages_count),
            'receive_messages' => MessageResource::collection($this->whenLoaded('receiveMessages')),
            'send_messages' => MessageResource::collection($this->whenLoaded('sendMessages')),
        ];
    }
}
