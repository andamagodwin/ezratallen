<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Chat extends Model

{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'receiver_id', 'count_status', 'type', 'message', 'delivered', 'status',
    ];



    /*
    public function getCreatedAtAttribute($value)
    {
        //$format = 'd/m/Y';

        return Carbon::parse($value)->format('d M Y, g:i A');
    }
    */
}
