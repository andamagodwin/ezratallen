<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class district extends Model
{

    
    
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'region_id','district_name',
    ];

    //district and product one to many relationship

    public function products(){
        return $this->hasMany('\App\product');
    }
}
