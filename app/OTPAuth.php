<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OTPAuth extends Model
{
    
    protected $fillable = [
        'otp_code',
        'email',
        'phone'
    ];
}
