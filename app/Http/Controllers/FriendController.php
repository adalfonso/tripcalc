<?php namespace App\Http\Controllers;

use App\Friend;
use App\Mail\AccountInvitation;
use App\PendingEmailTrip;
use App\Trip;
use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Mail;
use Response;
use Validator;

class FriendController extends Controller {

    public function searchEligibleFriends(Request $request, Trip $trip) {
    	$string = $request->input;
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
				'trip_id' => $trip->id
			]
		);

    	return $results;
    }

    public function inviteToTrip(Request $request, Trip $trip) {
        $this->trip = $trip;

        $invites = collect($request->friends)->unique();

        $emails = $this->filterByEmail($invites);
        $ids = $this->filterById($invites);

        if (! $this->validEmails($emails)) {
            return Response::json([
                'error' => ['An email you entered was of incorrect format.']
            ], 422);
        }

        if (! $this->validIds($ids)) {
            return Response::json([
                'error' => ['An error occurred while inviting your friend.']
            ], 422);
        }

        // Handle any emails that may already be tied to accounts
        if ($emails) {
            $users = User::whereIn('email', $emails)->get();

            $emails = $emails->reject(function($item) use ($users){
                return $users->contains('email', $item);
            });

            $ids = $ids->merge($users->pluck('id'));
        }

        // Handle any ids that may already be on the trip
        if ($ids) {
            $users = User::whereIn('id', $ids)->whereHas('trips', function($query) {
                $query->where('id', $this->trip->id);
            })->get();

            $ids = $ids->reject(function($item) use ($users){
                return $users->contains('id', $item);
            });
        }

       	DB::Transaction(function() use ($emails, $ids){
            $this->inviteById($ids);
            $this->inviteByEmail($emails);
        });
    }

    // filter methods, check emails and filter again if needed, testing with friendtest

    protected function filterById($collection) {
        return $collection->filter(function($item){
            return $item['type'] === 'id';
        })->pluck('data');
    }

    protected function filterByEmail($collection) {
         return $collection->filter(function($item){
            return $item['type'] === 'email';
        })->pluck('data');
    }

    protected function inviteByEmail($email) {
        if (! $email){
            return;
        }

        $email->each(function($email) {
            PendingEmailTrip::firstOrCreate([
                'email' => $email,
                'trip_id' => $this->trip->id
            ]);

            $this->sendInvitationEmail($email);
        });
    }

    protected function inviteById($id) {
        if (! $id) {
            return;
        }

        $this->trip->users()->attach($id);
    }

    protected function validEmails($emails) {
        return Validator::make(['email' => $emails->toArray()], [
            'email.*' => 'required|email'
        ])->passes();
    }

    protected function validIds($ids) {
        return Validator::make(['id' => $ids->toArray()], [
            'id.*' => 'required|numeric'
        ])->passes();
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

    public function sendInvitationEmail($user) {
        Mail::to($user)->send(new AccountInvitation);
    }
}
