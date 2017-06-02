<?php namespace App\Http\Middleware;

use Closure;
use Auth;

class CanDeleteProfilePost {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)  {

        $createdByUser = $request->post->created_by === Auth::id();

        $profileBelongsToUser =
            $request->post->postable_id === Auth::id() &&
            $request->post->postable_type === 'App\User';

        if (!($createdByUser || $profileBelongsToUser)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
