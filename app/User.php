<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'Users';
    protected $appends = array('events_count');

    public function events(){
        return $this->hasMany('App\Event');
    }

    public function getEventsCountAttribute() {
        $events = $this->events();
        $events = $events->count();
        return $events;
    }

    public function favorites(){
        return $this->hasMany('App\Category_favorite')->with('category');
    }




}
