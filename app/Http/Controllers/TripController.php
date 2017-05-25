<?php namespace App\Http\Controllers;

use App\Transaction;
use App\Trip;
use App\User;
use App\Post;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Response;
use Validator;

class TripController extends Controller {

    public function index () {
    	$trips = Trip::whereHas('users', function($query) {
            $query->where([
                'user_id' => Auth::user()->id,
                'active' => true
            ]);
        })->get()->sortByDesc('start_date');

    	return view('trips.index', compact('trips'));
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

        $activities = $this->activities($trip);
		$friendsInvitable = true;
		$sum = $trip->transactions->sum('amount');

    	return view('trips.show', compact(
			'activities', 'trip', 'sum', 'friendsInvitable'
		));
    }

    public function activities(Trip $trip, Request $request = null) {

        if ($request !== null && $request->has('oldestDate')) {
            $oldestDate = Carbon::parse($request->oldestDate['date']);
            $dateRange = "<";

        } else {
            $oldestDate = Carbon::now();
            $dateRange = "<=";
        }

        $transactions = Transaction::where([
                'trip_id' => $trip->id,
                ['created_at', $dateRange, $oldestDate]
            ])
            ->with('creator', 'updater')
            ->orderBy('created_at', 'DESC')
            ->limit(15)->get()
            ->map(function($item) {
                return (object) [
                    'type' => 'transaction',
					'id' => $item->id,
                    'creator' => $item->creator->fullname,
                    'updater' => $item->updater->fullname,
                    'created_at' => $item->created_at,
                    'date' => $item->dateFormat,
                    'dateForHumans' => $item->created_at->diffForHumans(),
                    'updatedDateForHumans' => $item->updated_at->diffForHumans(),
					'description' => $item->description,
					'amount' => $item->amount,
                    'hashtags' => $item->hashtags->pluck('tag')->toArray()
				];
            });


		$posts = Post::where([
                'trip_id' => $trip->id,
                ['created_at', $dateRange, $oldestDate]
            ])
			->with('user')
            ->orderBy('created_at', 'DESC')
            ->limit(15)->get()
			->map(function($item) {
				return (object) [
                    'type' => 'post',
					'id' => $item->id,
					'poster' => $item->user->fullname,
                    'created_at' => $item->created_at,
					'editable' => $item->created_by === Auth::id(),
					'content' => $item->content,
					'dateForHumans' => $item->created_at->diffForHumans()
				];
			});

        // Cannot merge into empty collection
        if ($transactions->isEmpty()) {
            $activities = $posts;

        } else {
            $activities = $transactions->merge($posts);
        }

        return $activities = $activities
            ->sortByDesc('created_at')
            ->take(15)
            ->values();
    }

    public function data(Trip $trip) {
        return $trip->makeHidden(['id', 'created_at', 'updated_at']);
    }

    public function eligibleFriends(Request $request, Trip $trip) {
    	$string = $request->input;

        $results = User

        // Name match
        ::where(function($query) use ($string) {
            $query->where('first_name', 'LIKE', "%$string%")
                ->orWhere('last_name', 'LIKE', "%$string%")
                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%$string%");
        })

        // Has friendship
        ->where(function($query){
            $query->whereHas('friendshipAsRequester', function($query){
                $query->where('recipient_id', Auth::id());

            })->orWhereHas('friendshipAsRecipient', function($query){
                $query->where('requester_id', Auth::id());
            });
        })

        // Not already in trip
        ->whereDoesntHave('trips', function($query) use ($trip){
            $query->where('id', $trip->id);
        })

        // Not already selected on invite friend form
        ->whereNotIn('id', collect($request->excluded))

        ->select('id', 'first_name', 'last_name')
        ->get();

    	return $results;
    }

    public function getPendingRequests() {
        return Auth::user()
            ->pendingTripRequests
            ->pluck('name', 'id');
    }

    public function resolveRequest(Request $request, Trip $trip) {
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

    public function travelers(Trip $trip) {
        $travelers = $trip->users->mapWithKeys(function($item) {
            return [
				$item->id => [
                    'id' => $item->id,
                    'full_name'   => $item->full_name,
                    'is_spender'  => false,
                    'split_ratio' => null
                ]
            ];
        });

		return [
			'travelers' => $travelers,
			'user'      => Auth::id()
		];
    }
}
