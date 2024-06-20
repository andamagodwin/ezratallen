<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductViewLog extends Model
{
    //
    protected $fillable = ['product_id', 'user_id', 'ip_address'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
