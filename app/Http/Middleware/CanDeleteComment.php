<?php namespace App\Http\Middleware;

use Closure;
use Auth;

class CanDeleteComment {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)  {

        $createdByUser = $request->comment->created_by === Auth::id();

        $post = $request->comment->post;

        $profileBelongsToUser =
            $post->postable_id === Auth::id() &&
            $post->postable_type === 'App\User';

        $postBelongsToUser = $post->created_by === Auth::id();

        if (!($createdByUser || $profileBelongsToUser || $postBelongsToUser)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
