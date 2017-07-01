<?php namespace App\Library\Report;

class DistributionReport extends Report {

	use AllUsers;

	protected $report;

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);

		$this->report = $this->allUsersMap();
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function generate() {
		$this->netBalanceAllUsers();

    	return $this->report->sortBy('total')->values()->all();
	}
}
