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
				});

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

		return $transaction = DB::transaction(function() use ($request, $trip) {

			$transaction = new Transaction([
				'amount'      => request('amount'),
				'date'        => request('date'),
				'description' => request('description')
			]);

			$transaction->trip_id = $trip->id;
			$transaction->created_by = Auth::user()->id;
			$transaction->updated_by = Auth::user()->id;
			$transaction->save();

			$spenders = $this->parseSpenders($request->split['travelers']);
			$transaction->users()->sync($spenders);

			foreach ($request->hashtags['items'] as $hashtag) {
				$hashtag = Hashtag::firstOrCreate(['tag' => $hashtag]);
				$hashtag->transactions()->attach($transaction->id);
			}

			return $transaction;
		});
	}

	public function update(Request $request, Trip $trip, Transaction $transaction) {
		$this->validateTransaction();

		return DB::transaction(function() use ($request, $transaction) {

			$transaction->date = request('date');
			$transaction->amount = request('amount');
			$transaction->description = request('description');
			$transaction->updated_by = Auth::user()->id;
			$transaction->save();

			$transaction->users()->sync(
				$this->parseSpenders($request->split['travelers'])
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
			'description' => 'max:50',
			'hashtags.items.*' => 'regex:/^[^,#\s]{1,32}$/',
			'split.travelers.*.split_ratio' => [
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
