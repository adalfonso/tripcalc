<?php namespace App\Library\Report;

class DistributionReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);

		$this->report = $this->allUsers()
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
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function generate() {
		$this->transactions->each(function($transaction) {

			// If user entered the transaction it is subtracted from their total
			$this->report
				->where('id', $transaction->created_by)
				->first()->total -= $transaction->amount;

			if ($this->isEvenSplit($transaction)) {
				return $this->splitEvenly($transaction);
			}

			return $this->splitUnevenly($transaction);
		});

    	return $this->report->sortBy('total')->values()->all();
	}

	/**
	 * Adjust totals for an evenly split transaction
	 * @param  App\Transaction
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function splitEvenly($transaction) {
		return $this->report->each(function($traveler) use($transaction) {
			$traveler->total += $transaction->amount / $this->report->count();
		});
	}

	/**
	 * Adjust totals for an unevenly split transaction
	 * @param  App\Transaction
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function splitUnevenly($transaction) {
		return $this->report->each(function($traveler) use($transaction) {
			$traveler->total += $transaction->amount
				* $this->splitRatio($traveler->id, $transaction)
				/ $this->splitTotal($transaction);
		});
	}

}
