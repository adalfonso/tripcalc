<?php namespace App\Library\Report;

use Carbon\Carbon;
use Auth;

class DetailedReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);
		$this->transactions = $this->userOnlyTransactions();
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function generate() {
		return $this->transactions->map(function($transaction) {
            return (object) [
                'date' => Carbon::parse($transaction->date)->format('m/d/Y'),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'creator' => $this->creatorName($transaction),
                'net' => money_format('%i', $this->transactionNet($transaction))
            ];
        });
	}

	/**
	 * Get the creator's name, or 'You' if paid by the user
	 * @param App\Transaction
	 * @return string
	 */
	public function creatorName($transaction) {
		return $transaction->created_by === Auth::id()
			? 'You'
			: $transaction->creator->first_name;
	}
}
