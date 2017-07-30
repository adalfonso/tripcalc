<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class TripIsActive {

    /**
     * Trip is in an active state
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!$request->trip->active) {
            $endpoint = '/trip/' . $request->trip->id;

            if (\Route::currentRouteName() === 'resolveTripRequest') {
                return response(['redirect' => $endpoint . '/removeRequest'], 422);
            }

            return redirect($endpoint);
        }

        return $next($request);
    }
}
