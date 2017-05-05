<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Trip;
use Auth;

class PostController extends Controller {

    public function store(Trip $trip, Request $request) {

        $this->validate(request(), [
            'content' => 'required'
        ]);

        $post = new Post($request->all());
        $post->trip_id = $trip->id;
        $post->created_by = Auth::id();
        $post->save();
    }
}
