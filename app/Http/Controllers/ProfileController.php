<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Friend;
use Auth;
use DB;

class ProfileController extends Controller {

    public function personal() {
    	$profile = Auth::user();
    	$user_id = $profile->id;

    	$user = User::where('id', $user_id)
    		->with('pendingFriendRequests', 'pendingTripRequests')
    		->first();

    	$friendRequests = Auth::user()->pendingFriendRequests->count();
    	$tripRequests = Auth::user()->pendingTripRequests->count();

        $posts = $this->posts($profile);

    	$friends = $user->friends;

    	return view('profile/personal', compact(
    		'friendRequests', 'tripRequests', 'friends', 'profile', 'posts'
    	));
    }

    public function show($username) {
    	$user = Auth::user();

    	if ($username === $user->username) {
    		return $this->personal();
    	}

    	$profile = User::select('id', 'username', 'first_name', 'last_name')
    		->where('username', $username)
    		->first();

    	$friendship = Friend::where([
    		'requester_id' => $user->id, 'recipient_id' => $profile->id
    	])->orWhere([
    		'recipient_id' => $user->id, 'requester_id' => $profile->id
    	])->first();

        $posts = is_null($friendship) ? null : $this->posts($profile);
		$friends = $profile->friends;

    	return view('profile/show', compact('profile', 'friendship', 'friends', 'posts'));

    }

    public function fetchMorePosts(User $user, Request $request) {
        if (!$request->has('oldestDate')) {
            return abort(400);

        }

        return $this->posts($user, $request->oldestDate);
    }

    public function posts(User $user, $date = null) {
        return $user->recentProfilePosts($date);
    }
}
