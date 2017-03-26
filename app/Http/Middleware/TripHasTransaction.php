<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TripHasTransaction {

    /**
     * Trip and transaction are linked
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $linked = $request->transaction->trip_id === $request->trip->id;

        if (!$linked) {
            abort(404);
        }

        return $next($request);
    }
}
