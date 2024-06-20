<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sellers extends Model
{

    protected $fillable=[
        'b_logo',
        'seller_id',
        'b_name' ,
        'b_phone',
        'b_email' ,
        'b_website',
        'b_country' ,
        'number_of_employee' ,
        'years_established',
        'b_description',
        'b_district',
        'b_subcounty',
        'b_physical_address',
        'b_postall_address',
        'stage'
    ];
    
}
