<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{



/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_name',
        'description',
        'picture',
        'picture_url',
    ];

    
     //one many user product relationship

     public function products(){
        return $this->hasMany('\App\product');
    }

}
