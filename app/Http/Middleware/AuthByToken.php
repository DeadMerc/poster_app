<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\Http\Controllers\Controller;


class AuthByToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //return $next($request);
        ///*
        //$controller = new Controller();
        if($request->header('token')){
            //echo 'find token';
            $user = User::where('token','=',$request->header('token'))->first();
            //print_r($user);
            if($user){
                //echo 'good token';
                $request->user = $user;
                return $next($request);
            }else{
                return redirect('/api/405');
            }
        }else{
            return redirect('/api/404');
        }

    }
}
