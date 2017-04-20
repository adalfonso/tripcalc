<?php namespace App\Http\Controllers;

use App\Transaction;
use App\Trip;
use App\User;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;

use Validator;

class TripController extends Controller {

	public function __construct() {
        $this->middleware('auth');
    }

    public function index () {
    	$trips = Trip::whereHas('users', function($query) {
            $query->where([
                'user_id' => Auth::user()->id,
                'active' => true
            ]);
        })->get()->sortByDesc('start_date');

    	return view('trips.dashboard', compact('trips'));
    }

    public function store(Request $request) {
        $this->validateTripData();

        $trip = DB::transaction(function(){
            $trip = Trip::create([
                'name' => request('name'),
                'start_date' => request('start_date'),
                'end_date' => request('end_date'),
                'budget' => request('budget'),
                'description' => request('description')
            ]);

            $trip->users()->attach(
                Auth::user()->id,
                ['active' => 1]
            );

            return $trip;
        });

        return $trip;
    }

    public function update(Trip $trip, Request $request) {
        $this->validateTripData();

        $trip->update([
            'name' => request('name'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'budget' => request('budget'),
            'description' => request('description')
        ]);

        return $trip;
    }

    public function destroy(Trip $trip, Request $request) {
        if (Hash::check($request->password, Auth::user()->password)) {
            Trip::destroy($trip->id);
            return ['success' => true];
        }

        return Response::json([
            'password' => ['The password you entered is incorrect.']
        ], 422);
    }

    public function show(Trip $trip) {
    	$transactions = Transaction::where("trip_id", $trip->id)
            ->with('creator')
    		->get();

    	$inTrip = true;

    	return view('trips.show', compact('transactions', 'trip', 'inTrip'));
    }

    public function data(Trip $trip) {
        return $trip->makeHidden(['id', 'created_at', 'updated_at']);
    }

    public function getPendingRequests() {
        return Auth::user()
            ->pendingTripRequests
            ->pluck('name', 'id');
    }

    public function resolveTripRequest(Request $request, Trip $trip) {
        $this->validate($request, [
            'resolution' => 'required|regex:/^-?1$/'
        ]);

        Auth::user()->trips()
            ->syncWithoutDetaching(
                [$trip->id => ['active' => $request->resolution]]
            );

        return $this->getPendingRequests();
    }

    public function validateTripData() {
        $messages = [
            'end_date.after_or_equal' => 'The end date can\'t be before the start date.'
        ];

        return $this->validate(request(), [
            'name' => 'required',
            'start_date' => 'required|date_format:Y-n-j',
            'end_date' => 'required|date_format:Y-n-j|after_or_equal:start_date',
            'budget' => 'nullable|regex:/^\d*(\.\d{1,2})?$/'
        ], $messages);
    }

    public function Travelers(Trip $trip) {
        $travelers = Trip::with('users')
            ->where('id', $trip->id)
            ->first()
            ->users;

        return $travelers->mapWithKeys(function($item){
           return [$item->id => [
                    'id' => $item->id,
                    'full_name'   => $item->full_name,
                    'is_spender'  => false,
                    'split_ratio' => ''
                ]
            ];
        });
    }
}