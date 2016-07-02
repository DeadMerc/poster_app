<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $appends = array('follow_count');

    public function photos(){
        return $this->hasMany('App\Photo');
    }
    public function follows(){
        return $this->hasMany('App\Event_follow');
    }

    public function getFollowCountAttribute() {
        $events = $this->follows();
        $events = $events->count();
        return $events;
    }


}
