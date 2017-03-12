<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use \App\Friend;
use \App\TripUser;

class RequestController extends Controller
{
    public function friend() {
    	return Auth::user()
    		->pendingFriendRequests
    		->pluck('full_name', 'id');
    }

    public function trip() {
    	return Auth::user()
    		->pendingTripRequests
    		->pluck('name', 'id');
    }

    public function resolveFriend(Request $request) {
        $this->validate($request, [
            'resolution' => 'required|regex:/^-?1$/',
            'id' => 'required|integer'
        ]);

        Friend::where([
            'requester_id' => $request->id,
            'recipient_id' => Auth::user()->id,
            'confirmed'    => 0
        ])->update(['confirmed' => $request->resolution]);

        return $this->friend();
    }

    public function resolveTrip(Request $request) {
        $this->validate($request, [
            'resolution' => 'required|regex:/^-?1$/',
            'id' => 'required|integer'
        ]);

        TripUser::where([
            'user_id' => Auth::user()->id,
            'trip_id' => $request->id,
            'active'  => 0
        ])->update(['active' => $request->resolution]);

        return $this->trip();
    }
}
