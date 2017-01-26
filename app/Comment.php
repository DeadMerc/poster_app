<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected  $table = 'comments';

    public function user() {
        return $this->hasOne('App\User_hidden_fields', 'id', 'user_id');
    }
}
