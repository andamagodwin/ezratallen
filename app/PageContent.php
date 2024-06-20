<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    //
    protected $fillable = [
        'title','page_type', 'page_content', 'page_id'
    ];
}
