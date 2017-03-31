<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use \App\User;
use \App\TripUser;

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
        $access = TripUser::where([
            ['trip_id', '=', $request->trip->id],
            ['user_id', '=', Auth::user()->id],
            ['active', '=', 1]
        ])->first();

        if (!$access) {
            return redirect('/trips/dashboard');
        }

        return $next($request);
    }
}
