<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunityQuestion extends Model
{
    //
    protected $fillable = [
        'community_id', 'user_id', 'topic', 'details', 'type', 'notify_reply', 
        'views_count', 'replies_count'
    ];
}
