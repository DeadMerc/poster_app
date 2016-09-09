<?php

namespace App\Http\Middleware;

use Closure;
use Response;
class AuthOnce
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
        if($request->getUser() !=  env('ADMIN_LOGIN') || $request->getPassword() !=  env('ADMIN_PASSWORD')) {
            $headers = array('WWW-Authenticate' => 'Basic');
            return Response::make('Invalid credentials.', 401, $headers);
        }else{
            return $next($request);
        }
    }
}
