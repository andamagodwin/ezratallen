<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInquiryReply extends Model
{


    protected $fillable = ['product_inquiry_id','sender_id', 'message'];

    public function inquiry()
    {
        return $this->belongsTo(ProductInquiry::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
