<?php

namespace App;

use App\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    protected $table = 'categories';
    public $appends = ['name'];

    public function getNameAttribute(){
        $lang = \Request::header('lang');
        if($lang == null){
            $lang = 'EN';
        }
        if($this->getAttribute('name_'.$lang)){
            return $this->getAttribute('name_'.$lang);
        }else{
            return $this->getAttribute('name_EN');

        }
    }
}
