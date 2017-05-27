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
        $hasFriendship = Auth::user()->friends
            ->contains($request->user->id);

        $isUsersProfile = Auth::id() === $request->user->id;

        if (!($hasFriendship || $isUsersProfile)) {
            return abort(403);
        }

        return $next($request);
    }
}
