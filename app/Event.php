<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'Events';

    public function photos(){
        return $this->hasMany('App\Photo');
    }

}
