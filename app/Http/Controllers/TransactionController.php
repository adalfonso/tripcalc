<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Hashtag;
use App\Transaction;
use App\Trip;
use App\User;

use DB;
use Auth;
use Hash;
use Response;

class TransactionController extends Controller {

	public function show(Trip $trip, Transaction $transaction) {
			$spenders = $transaction->allUsers;

			$travelers = $trip->allUsers
				->map(function($user) use ($spenders) {
					$spender = $spenders
						->where('type', $user->type)
						->where('id', $user->id);

					$split_ratio = $spender->isEmpty()
						? null
						: $spender->first()->pivot->split_ratio;

					$name = $user->type === 'virtual'
						? $user->name
						: $user->full_name;

					return [
						'id' => $user->id,
		                'type' => $user->type,
		                'full_name'   => $name,
		                'is_spender'  => $split_ratio !== null,
		                'split_ratio' => $split_ratio
					];
				})
				->sortBy('full_name')
				->values();

		return [
			'transaction' => $transaction,
			'hashtags'    => $transaction->hashtags->pluck('tag'),
			'travelers'   => $travelers,
			'creator'     => $transaction->creator->fullname,
			'user'        => Auth::id()
		];
	}

	public function store(Request $request, Trip $trip) {
		$this->validateTransaction();

		return DB::transaction(function() use ($request, $trip) {
			$this->transaction = new Transaction([
				'amount'      => request('amount'),
				'date'        => request('date'),
				'description' => request('description')
			]);

			$this->transaction->trip_id = $trip->id;
			$this->transaction->created_by = Auth::user()->id;
			$this->transaction->updated_by = Auth::user()->id;
			$this->transaction->save();

			$this->syncSpenders(collect($request->split));
			$this->syncHashtags(collect($request->hashtags));

			return $this->transaction;
		});
	}

	public function update(Request $request, Trip $trip, Transaction $transaction) {
		$this->validateTransaction();
		$this->transaction = $transaction;

		DB::transaction(function() use ($request) {
			$this->transaction->date = request('date');
			$this->transaction->amount = request('amount');
			$this->transaction->description = request('description');
			$this->transaction->updated_by = Auth::user()->id;
			$this->transaction->save();

			$this->syncSpenders(collect($request->split));
			$this->syncHashtags(collect($request->hashtags));
		});

		return $this->transaction;
	}

	public function destroy(Request $request, Trip $trip, Transaction $transaction) {
		// Verify user's password
		if (!Hash::check($request->password, Auth::user()->password)) {
			return Response::json([
				'password' => ['The password you entered is incorrect.']
			], 422);
		}

		$transaction->delete();

		return ['success' => true];
	}

	public function validateTransaction() {

		return $this->validate(request(), [
			'date' => 'required|date_format:Y-n-j',
			'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
			'description' => 'max:50',
			'hashtags.*' => 'regex:/^[^,#\s]{1,32}$/',
			'split.*.split_ratio' => [
				'nullable', 'regex:/(^\d*\.?\d+$)|(^\d+\.?\d*$)/'
			]
		]);
	}

	protected function syncHashtags($hashtags) {
		$hashtags = $hashtags->map(function($hashtag) {
			return Hashtag::firstOrCreate([ 'tag' => $hashtag ])->id;
		});

		$this->transaction->hashtags()->sync($hashtags);
	}

	protected function syncSpenders($users) {
		$regularSpenders = $this->parseSpenders($users->where('type', 'regular'));
		$this->transaction->users()->sync($regularSpenders);

		$virtualSpenders = $this->parseSpenders($users->where('type', 'virtual'));
		$this->transaction->virtualUsers()->sync($virtualSpenders);
	}

	protected function parseSpenders($spenders) {
		return $spenders ->filter(function($user) {
			return $user['is_spender'] && !empty($user['split_ratio']);
		})

		->keyBy('id')

		->map(function($spender) {
			return ['split_ratio' => $spender['split_ratio']];
		});
	}
}
