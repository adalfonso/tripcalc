<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Friend;
use \App\PendingEmailTrip;
use \App\Trip;
use \App\TripUser;
use \App\User;
use Auth;
use DB;
use Validator;
use Mail;

class FriendController extends Controller {

    public function searchEligibleFriends(Request $request, Trip $trip) {
    	$string = $request->input;
    	$trip_id = $request->trip_id;
    	$user_id = Auth::User()->id;

    	$results = DB::select('
    		SELECT u.id, first_name, last_name, username
			FROM users u
			JOIN friends f
			ON (
				(f.requester_id = u.id AND f.recipient_id = :user_id1)
				OR (f.recipient_id = u.id AND f.requester_id = :user_id2)
				AND confirmed = 1
			)
			AND (
				(first_name LIKE :search_input1 OR last_name LIKE :search_input2)
				OR (CONCAT(first_name, " ", last_name) LIKE :search_input3)
			)
			AND u.id NOT IN (SELECT user_id from trip_user where trip_id = :trip_id)
			ORDER BY last_name
			',
			[
				'user_id1' => Auth::user()->id,
                'user_id2' => Auth::user()->id,
				'search_input1' => "%$string%",
                'search_input2' => "%$string%",
				'search_input3' => "%$string%",
				'trip_id' => $trip_id
			]
		);

    	return $results;
    }

    public function inviteToTrip(Request $request, Trip $trip) {

    	$invites    = collect($request->friends);
        $addById    = $this->filterById($invites);
        $addByEmail = $this->filterByEmail($invites);

        // Checks if emails belongs to account already and moves to other collection
       	$addByEmail->each(function($email, $key) use($addById, $addByEmail) {
       		if ($user = User::where('email', $email)->first()) {

       			$addByEmail->forget($key);

       			if (! $addById->contains($user->id)) {
	       			$addById->push($user->id);
	       		}
       		}
       	});

       	DB::Transaction( function() use ($addByEmail, $addById, $request) {
	       	// Invite unregistered users
	        $addByEmail->each(function($email) use ($request) {
	            $invite = PendingEmailTrip::firstOrCreate([
	                'email' => $email,
	                'trip_id' => $request->trip_id
	            ]);
	        });

	        // Invite by active user
	        $addById->each(function($id) use ($request) {
	            $invite = TripUser::firstOrCreate([
	                'user_id' => $id,
	                'trip_id' => $request->trip_id
	            ]);

	            if ($invite->wasRecentlyCreated) {
	            	$invite->update(['active'  => 0]);
	    		}
	        });
        });

        // Send email invite to unregistered users
		$addByEmail->each(function($email) {
			$this->sendInvitationEmail($email);
		});
    }

    public function filterByEmail($invites) {
    	return $invites->filter(function($item){
            return $item['type'] === 'email';
        })->pluck('data');
    }

    public function filterById($invites) {
    	return $invites->filter(function($item){
            return $item['type'] === 'id';
        })->map(function($item) {
        	return (int) $item['data'];
        });
    }

    public function sendRequest(User $friend) {
        $user = Auth::user();

        Friend::updateOrCreate([
            'requester_id' => $user->id,
            'recipient_id' => $friend->id
        ]);
    }

    public function getPendingRequests() {
    	return Auth::user()
    		->pendingFriendRequests
    		->pluck('full_name', 'id');
    }

    public function resolveFriendRequest(Request $request, $friend) {
        $this->validate($request, [
            'resolution' => 'required|regex:/^-?1$/'
        ]);

        Friend::where([
            'requester_id' => $friend,
            'recipient_id' => Auth::user()->id,
            'confirmed'    => 0
        ])->update(['confirmed' => $request->resolution]);

        return $this->getPendingRequests();
    }

    public function sendInvitationEmail($email) {
    	Mail::send('emails.account.invitation', [], function ($message) use ($email) {
		    $message->to($email);
		    $message->from('tripcalcapp@gmail.com', 'TripCalc Bot');
		    $message->subject('Greetings from TripCalc!');
		});
    }
}
