<?php namespace App\Library\Report;

class TopSpendersReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);
		$this->transactions = $this->transactions->groupBy('created_by');
	}

	/**
	 * Generate report data
	 * @return array
	 */
	public function generate() {
		$spend = $this->transactions->map(function($spendersTransactions) {
                $creator = $spendersTransactions->first()->creator;
                $itemSum = $spendersTransactions->sum('amount');

                return (object) [
                    'name' => $creator->first_name . ' ' . $creator->last_name,
                    'currency' => number_format($itemSum, 2),
                    'sum' => $itemSum,
                    'percent' => round($itemSum / $this->sum() * 100)
                ];
            })
			->filter(function($spender) {
                return $spender->percent > 1;
            })
			->sortByDesc('sum')
			->take(5);

        return [
            'spend' => $spend->values(),
            'max' => $spend->max('sum')
        ];
	}
}
