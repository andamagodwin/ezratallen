<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class subCategory extends Model
{


   
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id','subcat_name',
    ];

    //subcategory and product one to many relationship
    public function products(){
        return $this->hasMany('\App\product');
    }
}
