<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use \App\User;

class CanAccessTrip {

    /**
     * User has access to trip
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
    	$trip = Auth::User()->trips->where('id', $request->trip->id);

        if ($trip->count() === 0) {
            return redirect('/trips/dashboard');
        }

        return $next($request);
    }
}
