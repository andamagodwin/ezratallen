<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{

    protected $fillable = ['user_id', 'category_id', 'name', 'description', 'quantity', 'unit', 'picture'];
}
