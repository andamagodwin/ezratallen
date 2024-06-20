<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductInquiry extends Model
{



    protected $fillable = ['product_id', 'user_id', 'price', 'description', 'quantity', 'contact'];

    public function replies()
    {
        return $this->hasMany(ProductInquiryReply::class, 'product_inquiry_id')->with('sender');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(product::class);
    }
}
