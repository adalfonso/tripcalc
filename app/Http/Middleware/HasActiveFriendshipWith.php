<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class HasActiveFriendshipWith {

    /**
     * User has access to trip
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $access = Auth::user()->friends
            ->contains($request->user->id);

        if (!$access) {
            return abort(401);
        }

        return $next($request);
    }
}
