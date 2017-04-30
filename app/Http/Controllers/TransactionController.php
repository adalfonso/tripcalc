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

	public function byTrip(Trip $trip, Transaction $transaction) {
		$travelers = DB::select('
			SELECT * FROM (
				SELECT u.id AS join_on, u.id AS id,
					CONCAT(u.first_name, " ", u.last_name) AS full_name
				FROM users u
				JOIN trip_user tu ON u.id = tu.user_id
				JOIN trips t      ON tu.trip_id = t.id
				WHERE t.id = :trip_id
			) tb1
			LEFT JOIN (
				SELECT user_id AS join_on, split_ratio, count(*) AS is_spender
				FROM transaction_user
				WHERE transaction_id = :transaction_id
				GROUP BY user_id, split_ratio
			) tb2
			ON tb1.join_on = tb2.join_on
			', [
				'trip_id'        => $trip->id,
				'transaction_id' => $transaction->id
			]);

		return [
			'transaction' => $transaction,
			'hashtags'    => $transaction->hashtags->pluck('tag'),
			'travelers'   => $travelers,
			'creator'     => $transaction->creator->fullname
		];
	}

	public function store(Request $request, Trip $trip) {
		$this->validateTransaction();

		return $transaction = DB::transaction(function() use ($request, $trip) {

			$transaction = Transaction::create([
				'trip_id' => $trip->id,
				'amount' => request('amount'),
				'date' => request('date'),
				'description' => request('description'),
				'created_by' => Auth::user()->id,
				'updated_by' => Auth::user()->id
			]);

			$spenders = $this->parseSpenders($request->travelers);
			$transaction->users()->sync($spenders);

			foreach ($request->hashtags['items'] as $hashtag) {
				$hashtag = Hashtag::firstOrCreate([ 'tag' => $hashtag ]);
				$hashtag->transactions()->attach($transaction->id);
			}

			return $transaction;
		});
	}

	public function update(Request $request, Trip $trip, Transaction $transaction) {
		$this->validateTransaction();

		return DB::transaction(function() use ($request, $transaction) {

			$transaction->update([
				'date' => request('date'),
				'amount' => request('amount'),
				'description' => request('description'),
				'updated_by' => Auth::user()->id
			]);

			$transaction->users()->sync(
				$this->parseSpenders($request->travelers)
			);

			$hashtags = collect($request->hashtags['items'])->map(function($hashtag) {
				return Hashtag::firstOrCreate([ 'tag' => $hashtag ])->id;
			});

			$transaction->hashtags()->sync($hashtags);

			return $transaction;
		});
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
			'hashtags.items.*' => 'regex:/^[^,\s]{1,32}$/',
			'travelers.*.split_ratio' => [
				'nullable', 'regex:/(^\d*\.?\d+$)|(^\d+\.?\d*$)/'
			]
		]);
	}

	protected function parseSpenders($travelers) {
		return collect($travelers)

		->filter(function($spender) {
			return $this->isSpender($spender);
		})

		->keyBy('id')

		->map(function($spender){
			return ['split_ratio' => $spender['split_ratio']];
		});
	}

	protected function isSpender($spender) {
		return $spender['is_spender'] && !empty($spender['split_ratio']);
	}
}
