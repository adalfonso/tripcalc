<?php namespace App\Http\Middleware;

use Closure;
use Auth;

class OwnsPost {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)  {

        if ($request->post->created_by !== Auth::id()) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
