<?php

namespace App\Providers;

use App\Helpers\LiqPay;
use Illuminate\Support\ServiceProvider;

class LiqPayProvider  extends ServiceProvider
{


    public function register() {
        $this->app->bind('App\Helpers\LiqPay',function(){
            return new LiqPay('i86486134497','0foihDsSyU2vjha4jAq5YNIwNoudMpMsy7JMqXKT');
        });
    }
    public function boot() {
        //
    }
}
