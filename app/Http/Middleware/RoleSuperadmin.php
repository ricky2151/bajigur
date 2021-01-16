<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\LoginFailedException;

class RoleSuperadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if($user != null)
        {
            $currentRole = $user->role->name;
            if ($currentRole == 'superadmin')
            {
                return $next($request);
            }
            else
            {
                throw new LoginFailedException("You Are Not Admin !");
            }
        }
        else
        {
            throw new LoginFailedException("You Are Not Admin !");
        }
    }
}
