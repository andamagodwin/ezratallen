<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'subject', 'message'
    ];

}
