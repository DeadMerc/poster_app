<?php

namespace App;

class User_hidden_fields extends User
{

    protected $table = 'users';
    protected $appends = array('events_count');
    protected $hidden = [
        'password',
        'imei',
        'token',
        'balance',
        'device_type',
        'device_token',
        'created_at',
        'updated_at',
    ];

    public function sessions(){
        return $this->hasMany('App\EventCinemaUser','user_id','id')->with('event');
    }
}
