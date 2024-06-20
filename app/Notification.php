<?php

namespace App;

use App\product;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'body', 'read_status',
        'screen',
        'count_status', 'status', 'profile_id', 'product_id', 'mobile_data', 'screen_object', 'web_url'
    ];



    public function getProductIdAttribute($value)
    {

        $product = product::find($value);

        if ($product) {
            return $product->picture;
        }

        return false;
    }

    public function getProfileIdAttribute($value)
    {

        $profile = User::find($value);


        return $profile;
    }
}
