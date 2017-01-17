<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $appends = array('follow_count','price_range');

    public function getPriceRangeAttribute(){
        $price = $this->attributes['price'];
        $price = explode('..',$price);
        if(empty($price[1])){
            $price[1] = $price[0];

        }
        return [
            'from'=>(int)$price[0],
            'to'=>(int)$price[1]
        ];
    }

    public function getPriceAttribute($v){
        $v = explode('..',$v);
        return (int) $v[0];
    }

    public function photos() {
        return $this->hasMany('App\Photo');
    }

    public function comments(){
        return $this->hasMany('App\Comment');
    }

    public function follows() {
        return $this->hasMany('App\Event_follow');
    }

    public function user() {
        return $this->hasOne('App\User_hidden_fields', 'id', 'user_id');
    }

    public function cinema(){
        return $this->hasMany('App\EventCinemaUser','event_id','id')
            ->with('user')
            ;
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
