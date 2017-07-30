<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class TripIsActive {

    /**
     * User has access to trip
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!$request->trip->active) {
            return redirect('/trip/' . $request->trip->id);
        }

        return $next($request);
    }
}
