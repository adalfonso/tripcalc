<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class CanAccessPost {

    /**
     * User has access to trip
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $model = $request->post->postable;
        $base = class_basename($model);
        $user = Auth::user();

        if ($base === 'Trip') {
            if ($user->trips->where('id', $model->id)->count()) {
                return $next($request);
            }

        } else if ($base === 'User') {
            if ($request->post->postable_id === $user->id || $user->isFriendsWith($model)) {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
