<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $appends = array('follow_count');


    public function photos() {
        return $this->hasMany('App\Photo');
    }

    public function follows() {
        return $this->hasMany('App\Event_follow');
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function getFollowCountAttribute() {
        $events = $this->follows();
        $events = $events->count();
        return $events;
    }

    protected static function boot() {
        parent::boot();
        static::addGlobalScope('old', function(Builder $builder) {
            $builder->where('date_stop', '>', date("Y-m-d H:i:s"));
        });
    }


}
