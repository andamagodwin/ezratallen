<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductLikeLog extends Model
{
    //
    protected $fillable = ['product_id', 'user_id'];
}
