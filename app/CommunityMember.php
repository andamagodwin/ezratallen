<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunityMember extends Model
{
    //
    protected $fillable = [
        'user_id', 'community_id'
    ];
}
