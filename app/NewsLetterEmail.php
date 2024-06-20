<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsLetterEmail extends Model
{
    //
    protected $fillable = [
        'email', 'first_name', 'last_name'
    ];
}
