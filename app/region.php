<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{

/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'region_name',
    ];

    //product and region one to many relationship
    public function products(){
        return $this->hasMany('\App\product');
    }
}
