<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

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
        $access = Auth::user()->activeTrips
            ->contains($request->trip->id);

        if (!$access) {
            return redirect('/trips');
        }

        return $next($request);
    }
}
