<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\User;

use Closure;

class AdminMiddleWare
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
        if (Auth::user()->user_type=='admin') {
             return $next($request);
        }

        return "Your not an admininistrator to access these resources";
       
    }
}
