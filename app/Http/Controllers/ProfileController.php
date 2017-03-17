<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\User;
use \App\Friend;

use \Auth;
use \DB;

class ProfileController extends Controller {

	public function __construct() {
        $this->middleware('auth');
    }

    public function personal() {
    	$profile = Auth::user();
    	$user_id = $profile->id;

    	$user = User::where('id', $user_id)
    		->with('pendingFriendRequests', 'pendingTripRequests')
    		->first();

    	$friendRequests = Auth::user()->pendingFriendRequests->count();
    	$tripRequests = Auth::user()->pendingTripRequests->count();

    	$friends = $user->friends;

    	return view("profile/personal", compact(
    		"friendRequests", "tripRequests", "friends", "profile"
    	));
    }

    public function show($username) {
    	$user = Auth::user();

    	if ($username === $user->username) {
    		return $this->personal();
    	}

    	$profile = User::select("id", "username", "first_name", "last_name")
    		->where("username", $username)
    		->first();

    	$friendship = Friend::where([
    		'requester_id' => $user->id, 'recipient_id' => $profile->id
    	])->orWhere([
    		'recipient_id' => $user->id, 'requester_id' => $profile->id
    	])->first();

    	return view("profile/show", compact("profile", "friendship"));

    }
}