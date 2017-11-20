<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Comment;

class CommentController extends Controller {

	public function update(Comment $comment, Request $request) {
        $this->validate(request(), [ 'content' => 'required|max:255' ]);
        $comment->update($request->all());
    }

    public function destroy(Comment $comment) {
        $comment->delete();
    }
}
