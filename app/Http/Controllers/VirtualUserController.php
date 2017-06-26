<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trip;
use App\VirtualUser;

class VirtualUserController extends Controller {

    public function index(Trip $trip) {
        return $trip->virtualUsers()->with('transactions')
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'deleteable' => $user->transactions->isEmpty()
                ];
            });
    }

    public function store(Request $request, Trip $trip) {
        $names = $trip->virtualUsers->pluck('name')->implode(',');

        $this->validate($request, [
            'name' => 'required|max:63|notIn:' . $names
        ]);

        $user = new VirtualUser;
        $user->name = $request->name;
        $user->trip_id = $trip->id;
        $user->save();

        return $this->index($trip);
    }

    public function destroy(Trip $trip, VirtualUser $virtualUser) {
        if ($virtualUser->transactions->count() > 0) {
            return;
            // some error
        }

        $virtualUser->delete();

        return $this->index($trip);
    }
}
