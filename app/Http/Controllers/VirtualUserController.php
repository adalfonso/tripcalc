<?php namespace App\Http\Controllers;

use App\Transaction;
use App\Trip;
use App\VirtualUser;
use App\Library\VirtualUser\Merger;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function update(Request $request, Trip $trip, VirtualUser $virtualUser) {
        $names = $trip->virtualUsers->pluck('name')->implode(',');

        $this->validate($request, [
            'name' => 'required|max:63|notIn:' . $names
        ]);

        $virtualUser->name = $request->name;
        $virtualUser->save();

        return $this->index($trip);
    }

    public function destroy(Trip $trip, VirtualUser $virtualUser) {
        if ($virtualUser->transactions->count() > 0) {
            return response('Cannot delete a virtual user who has transactions', 422);
        }

        $virtualUser->delete();

        return $this->index($trip);
    }

    public function merge(Request $request, Trip $trip, VirtualUser $virtualUser) {
        $merge = new Merger($request, $trip, $virtualUser);

        if (! $merge->canMerge()) {
            return $merge->conflictResponse();
        }

        $merge->merge();
    }
}
