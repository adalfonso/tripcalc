<?php namespace App\Http\Middleware;

use Closure;
use Auth;

class IsCurrentUser {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)  {

        if ($request->user->id !== Auth::id()) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
