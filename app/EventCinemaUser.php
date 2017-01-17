<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventCinemaUser extends Model
{
    protected $table = 'event_cinema_users';
    public $timestamps =false;
    protected $appends = array('price_range');

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

    public function user(){
        return $this->hasOne('App\User_hidden_fields','id','user_id');
    }
    public function event(){
        return $this->hasOne('App\Event','id','event_id');
    }
}
