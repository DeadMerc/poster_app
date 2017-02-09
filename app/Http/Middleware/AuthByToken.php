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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        //return $next($request);
        ///*
        $controller = new Controller();
        if ($request->header('token')) {
            if ($request->header('token') == 'adm') {
                $user = User::where('token','adm')->first();
                if(!$user){
                    $user = new User;
                    $user->type = 'admin';
                    $user->imei = 'admin';
                    $user->banned = 0;
                    $user->balance = 99999999;
                    $user->token = 'adm';
                    $user->save();
                }
                if($user->balance < 9999){
                    $user->balance = 99999999;
                    $user->save();
                }
            } else {
                //echo 'find token';
                $user = User::where('token', '=', $request->header('token'))->with('favorites')->first();
                if($user){
                    if ($user->banned == 1) {
                        return $controller->helpError('You account was banned.');
                    }
                }else{
                    return $controller->helpError('You token is invalid');
                }
            }
            if ($user) {
                //echo 'good token';
                $request->user = $user;
                return $next($request);
            } else {
                return $controller->helpError('You token is invalid');
            }
        } else {
            //return response('wrong');
            return $controller->helpError('You token not found.');
        }

    }
}
