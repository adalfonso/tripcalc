<?php namespace App\Library\Report;

class CloseoutReport extends Report {

	use AllUsers;

	protected $report;

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);

		$this->report = $this->allUsers()->map(function($user) {
			return (object) [
				'id'         => $user->id,
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
				'total'      => 0,
				'credits'   => collect([]),
				'debits'   => collect([])
			];
		});
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	protected function generate() {
		$this->netBalanceAllUsers();
		$this->report = $this->report->sortByDesc('total');

		return [
			'spenders' => $this->balance()->values(),
			'allUsers' => $this->allUsers()->pluck('full_name', 'id')
		];
	}
	/**
	 * Balance totals for all users
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function balance() {
		return $this->report->each(function($debtor) {

			if ($this->isFractionalCents($debtor->total)) {
				return $debtor->total = 0;
			}

			return $this->payDebt($debtor);
		});
	}

	/**
     * Determine if amount is less than a penny
     * @return boolean
     */
	public function isFractionalCents($amount) {
		$oneCent = .01;

		return bccomp(abs($amount), $oneCent, 10) === -1;
	}

	/**
	 * Balance a debtors debt with the spenders
	 * @return void
	 */
	public function payDebt($debtor) {
		$this->report->reverse()->each(function($spender) use ($debtor) {
			if ($this->zeroBalance($debtor, $spender)) {
				return;
			}

			if ($this->canPayInFull($debtor, $spender)) {
				return $this->creditSpenderFull($debtor, $spender);
			}

			return $this->creditSpenderPartial($debtor, $spender);
		});
	}

	/**
	 * Determine if either party has a zero balance
	 * @return boolean
	 */
	public function zeroBalance($debtor, $spender) {
		return $debtor->total === 0 || $spender->total === 0;
	}

	/**
	 * Determine if the spender can be paid in full
	 * @return boolean
	 */
	public function canPayInFull($debtor, $spender) {
		return $debtor->total >= abs($spender->total);
	}

	/**
	 * Fully pay a spender
	 * @return boolean || void
	 */
	public function creditSpenderFull($debtor, $spender) {
		$debtor->debits->put($spender->id, abs($spender->total));
		$spender->credits->put($debtor->id, abs($spender->total));
		$debtor->total = bcadd($debtor->total, $spender->total, 2);
		$spender->total = 0;

		// Exit out of loop if debtor has paid in full
		if (bccomp($debtor->total, 0, 10) === 0 ) {
			return false;
		}
	}

	/**
	 * Partially pay a spender
	 * @return boolean
	 */
	public function creditSpenderPartial($debtor, $spender) {

		$debtor->debits->put($spender->id, abs($debtor->total));
		$spender->credits->put($debtor->id, abs($debtor->total));
		$spender->total = bcadd($spender->total, $debtor->total, 2);
		$debtor->total = 0;

		// Break out of each method early when debtor has paid in full
		return false;
	}
}
