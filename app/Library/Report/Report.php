<?php namespace App\Library\Report;

use App\Transaction;

class Report {

	protected $report;
	protected $transactions;
	protected $trip;

	public function __construct(\App\Trip $trip) {
		$this->trip = $trip;

		$this->transactions = Transaction::where("trip_id", $trip->id)
            ->with('users')->get();

    	// Users who have entered a transaction
		$usersFromSpend = $this->transactions->pluck('creator');

		// Users who have a split ratio for a transaction
		$usersFromSplit = $this->transactions
			->pluck('users')->collapse();

		// Users who are currently on the trip
		$usersFromTrip = $trip->users;

		$this->report = $usersFromSpend
			->merge($usersFromSplit)->merge($usersFromTrip)
			->unique('id')
			->map(function($user) {
				return (object) [
					'id'         => $user->id,
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'total'      => 0
				];
			});
	}

	/**
	 * Determine if a user_id is a spender on a transaction
	 * @param  integer
	 * @param  Transaction
	 * @return boolean
	 */
	public function generate() {
		$this->transactions->each(function($transaction) {

			// When user enters a transaction it is subtracted from their total
			$this->report
				->where('id', $transaction->created_by)
				->first()->total -= $transaction->amount;

			if ($this->isEvenSplit($transaction)) {
				return $this->evenSplit($transaction);
			}

			return $this->unevenSplit($transaction);
		});

    	return $this->report;
	}

	/**
	 * Determine if a user_id is a spender on a transaction
	 * @param  integer
	 * @param  Transaction
	 * @return boolean
	 */
	public function isSpender($id, $transaction) {
		return $transaction->users->where('id', $id)->count() > 0;
	}

	/**
	 * Determine if transaction is split evenly between everyone
	 * @param  Transaction
	 * @return boolean
	 */
	public function isEvenSplit($transaction) {
		return $transaction->users->count() === 0;
	}

	/**
	 * Adjust totals for an evenly split transaction
	 * @param  Transaction
	 * @return Collection
	 */
	public function evenSplit($transaction) {
		return $this->report->each(function($traveler) use($transaction) {
			$traveler->total += $transaction->amount / $this->report->count();
		});
	}

	/**
	 * Adjust totals for an unevenly split transaction
	 * @param  Transaction
	 * @return Collection
	 */
	public function unevenSplit($transaction) {
		return $this->report->each(function($traveler) use($transaction) {
			$traveler->total += $transaction->amount
				* $this->splitRatio($traveler->id, $transaction)
				/ $this->splitTotal($transaction);
		});
	}

	/**
	 * Sum the total split ratios for a transaction
	 * @param  Transaction
	 * @return number
	 */
	public function splitTotal($transaction) {
		return $transaction->users->sum('pivot.split_ratio');
	}

	/**
	 * Get the split ratio for a user_id on a transaction
	 * @param  integer
	 * @param  Transaction
	 * @return number
	 */
	public function splitRatio($id, $transaction) {
		if (! $this->isSpender($id, $transaction)) {
			return 0;
		}

		return $transaction->users
			->where('id', $id)->first()
			->pivot->split_ratio;
	}

}
