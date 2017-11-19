<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Trip;
use App\User;
use Auth;

class PostController extends Controller {

    public function show(Post $post) {
        $post->load('comments.user', 'postable', 'user');
        $base = strtolower(class_basename($post->postable));
		$type = $base === 'user' ? 'profile' : $base;
        $isOwner = $post->postable_type === 'App\User'
            && $post->postable_id === \Auth::id();

        $mapped = (object) [
            'type' => 'post',
            'id' => $post->id,
            'poster' => $post->user->fullname,
            'created_at' => $post->created_at,
            'editable' => $post->created_by === Auth::id(),
            'content' => $post->content,
            'dateForHumans' => $post->created_at->diffForHumans(),
            'comments' => $post->comments
        ];

        return view('post.show', compact('mapped', 'post', 'type', 'isOwner'));
    }

    public function commentOnTrip(Trip $trip, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->comments()->create($request->all());
        $post->notifyOthersOnce('comment');

        $post->load('comments.user');

        return $post->comments;
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

        $post->load('comments.user');

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
