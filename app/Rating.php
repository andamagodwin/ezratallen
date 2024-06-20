<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    //

    protected $appends = ['owner_id', 'reply'];


    public function getOwnerIdAttribute()
    {

        $product = product::find($this->product_id);
        if ($product) {
            return $product->user_id;
        }
    }


    public function getReplyAttribute()
    {


        return ReviewReply::where('rating_id', $this->id)->get();
    }
}
