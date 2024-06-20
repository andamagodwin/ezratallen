<?php

namespace App;

use Illuminate\Support\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        // 'region_id', 'district_id',
        'user_id', 'negotiation_status', 'is_inquiry', 'brand_name', 'minimum_order_qty',
        'min_price', 'max_price', 'is_approved',
        'category_id', 'low_bid_qty',  'sub_category_id', 'product_title', 'picture', 'price', 'view_count', 'description', 'longitude', 'latitude', 'available_quantity', 'units', 'address', 'currency'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getCreatedAtAttribute($value)
    {
        //IsaacOc adds this condition to enable
        //unite testing using phpunit
        
        if($value != ''){
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
        }
    }
}
