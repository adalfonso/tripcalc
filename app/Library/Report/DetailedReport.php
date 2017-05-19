<?php namespace App\Library\Report;

use Carbon\Carbon;
use Auth;

class DetailedReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip, $this->userOnlyTransactions($trip));
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	protected function generate() {
		return $this->transactions->map(function($transaction) {
            return (object) [
                'date' => Carbon::parse($transaction->date)->format('m/d/Y'),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'creator' => $this->creatorName($transaction),
				'isCreator' => $transaction->created_by === Auth::id(),
                'net' => money_format('%i', $this->netTransaction($transaction)),
				'splitTotal' => number_format($transaction->splitTotal, 2),
				'splitType' => $transaction->splitType,
				'split' => $transaction->users,
				'userSplit' => number_format($transaction->userSplit, 2 )
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
