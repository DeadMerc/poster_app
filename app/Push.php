<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    protected $table = 'push_history';
    public $timestamps = false;

    public function user(){
        return $this->hasOne('App\User','id','send_to');
    }
}
