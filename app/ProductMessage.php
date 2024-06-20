<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductMessage extends Model
{
    //
    protected $fillable = ['product_id', 'content', 'status', 'attachment'];
}
