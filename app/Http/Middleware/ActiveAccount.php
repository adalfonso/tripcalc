<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ActiveAccount
{
    /**
     * Users account is active
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!Auth::user()->activated) {
            return redirect('/user/activation/pending');
        }

        return $next($request);
    }
}
