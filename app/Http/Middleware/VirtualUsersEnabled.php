<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class VirtualUsersEnabled {

    /**
     * Virtual user setting is enabled
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!$request->trip->virtual_users) {
            return redirect('/trip/' . $request->trip->id);
        }

        return $next($request);
    }
}
