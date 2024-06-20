<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunityQuestionReply extends Model
{
    //
    protected $fillable = [
        'community_question_id', 'user_id', 'text', 'image', 'emoji', 'file', 
        'likes_count', 'replies_count'
    ];
}
