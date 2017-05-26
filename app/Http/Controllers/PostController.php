<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Trip;
use Auth;

class PostController extends Controller {

    public function storeForTrip(Trip $trip, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post = $trip->posts()->create($request->all());
    }

	public function updateForTrip(Trip $trip, Post $post, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $post->update($request->all());
    }

    public function destroyForTrip(Trip $trip, Post $post) {
        $post->delete();
    }
}
