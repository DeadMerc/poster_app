<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category_favorite extends Model
{
    protected $table = 'Categories_favorite';

    public function category(){
        return $this->hasOne('App\Category','id','category_id');
    }
}
