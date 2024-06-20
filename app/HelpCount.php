<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpCount extends Model
{
    protected $fillable = [
        'user_id', 'yes_count', 'no_count', 'status'
    ];
}
