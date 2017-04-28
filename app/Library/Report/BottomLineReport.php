<?php namespace App\Library\Report;

use Auth;

class BottomLineReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);
		$this->transactions = $this->userOnlyTransactions();
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function generate() {
		return $this->transactions->reduce(function($carry, $transaction) {
			return $carry + $this->transactionNet($transaction);
		});
	}
}
