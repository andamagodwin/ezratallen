<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class ProductRequestChat extends Model
{
    protected $fillable = [
        'receiver_id',
        'message',
        'product_request_id'
    ];




    protected $casts = [
        'seen_at' => 'datetime',
        'message_deleted_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
