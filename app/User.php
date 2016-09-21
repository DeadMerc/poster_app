<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    protected $table = 'users';
    protected $appends = array('events_count');
    protected $hidden = ['password'];

    /*
     * TODO:Security problem in future partly solved
     */

    public function getTokenAttribute($v){
        $headers = apache_request_headers();
        if(isset($headers['Token'])){
            return null;
        }else{
            return $v;
        }
    }

    public function getEventsCountAttribute() {
        $events = $this->events();
        $events = $events->count();
        return $events;
    }
    public function events(){
        return $this->hasMany('App\Event')->with('photos');
    }

    public function followsEvents(){
        return $this->belongsToMany('App\Event','events_follow','user_id','event_id')->with('photos','user');
    }



    public function favorites(){
        return $this->hasMany('App\Category_favorite')->with('category');
    }




}
