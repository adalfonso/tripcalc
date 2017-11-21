<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Trip;
use App\User;
use Auth;

class PostController extends Controller {

    public function show(Post $post) {
        $post->load('comments.user', 'comments.post', 'postable', 'user')->map();
        $base = strtolower(class_basename($post->postable));
		$type = $base === 'user' ? 'profile' : $base;

        $post->comments->each(function($comment) {
            $comment->dateForHumans = $comment->diffForHumans;
        });

        return view('post.show', compact('post', 'type'));
    }

    public function commentOnTrip(Trip $trip, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->comments()->create($request->all());
        $post->notifyOthersOnce('comment');

        $post->load('comments.user', 'comments.post');

         return $post->comments->each(function($comment) {
            $comment->dateForHumans = $comment->diffForHumans;
        });
    }

    public function storeForTrip(Trip $trip, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post = $trip->posts()->create($request->all());

        $trip->others->each(function($user) use ($post) {
            $post->notifications()->create([
                'subtype' => 'trip',
                'user_id' => $user->id,
            ]);
        });
    }

	public function updateForTrip(Trip $trip, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->update($request->all());
    }

    public function destroyForTrip(Trip $trip, Post $post) {
        $post->delete();
    }

    public function commentOnProfile(User $user, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->comments()->create($request->all());
        $post->notifyOthersOnce('comment');

        $post->load('comments.user', 'comments.post');

        return $post->comments->each(function($comment) {
            $comment->dateForHumans = $comment->diffForHumans;
        });
    }

    public function storeForProfile(User $user, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post = $user->profilePosts()->create($request->all());

        if ($user->id !== Auth::id()) {
            $post->notifyOne($user, 'profile');
        }
    }

    public function updateForProfile(User $user, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->update($request->all());
    }

    public function destroyForProfile(User $user, Post $post) {
        $post->delete();
    }

}
