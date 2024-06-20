<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{


    //
    protected $fillable = [
        'product_id', 'qty', 'unit', 'customer_id', 'instruction', 'phone',


        'amount', 'address', 'name', 'order_status', 'view_status', 'seller_id'
    ];


    public function getCreatedAtAttribute($value)
    {
        //$format = 'd/m/Y';



        return Carbon::parse($value)->format('d M Y');
    }
}
