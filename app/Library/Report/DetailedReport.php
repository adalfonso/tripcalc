<?php namespace App\Library\Report;

use Carbon\Carbon;
use Auth;

class DetailedReport extends Report {

	public function __construct(\App\Trip $trip) {
		parent::__construct($trip);
		$this->transactions = \App\Transaction::where("trip_id", $trip->id)
            ->where(function($query) {
              $query->where('created_by', Auth::id())
                    ->orWhereHas('users', function($user) {
                        $user->where('user_id', Auth::id());
                    })->doesntHave('users', 'or');
            })
            ->with('creator', 'users')
            ->orderBy('date')
            ->get();
	}

	/**
	 * Generate report data
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function generate() {
		return $this->transactions->map(function($transaction) {

            $net = $transaction->created_by === Auth::id()
                ? $this->paidByUser($transaction)
                : $this->paidByOther($transaction);

            $creator = $transaction->created_by === Auth::id()
                ? 'You'
                : $transaction->creator->first_name;

            return (object) [
                'date' => Carbon::parse($transaction->date)->format('m/d/Y'),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'creator' => $creator,
                'net' => money_format('%i', $net)
            ];
        });
	}

	/**
	 * Get net transaction amount paid by the user
	 * @param  App\Transaction
	 * @return number
	 */
	public function paidByUser($transaction) {

		if ($this->isEvenSplit($transaction)) {
			$users = $transaction->trip->users->count();

			return $transaction->amount * ($users - 1) / $users;

		} elseif ($this->isPersonalTransaction($transaction)) {
			return 0;
		}

		return $transaction->amount - $this->oweForUnevenSplit($transaction);
	}

	/**
	 * Get net transaction amount paid by another the user
	 * @param  App\Transaction
	 * @return number
	 */
	public function paidByOther($transaction) {

		if ($this->isEvenSplit($transaction)) {
			$users = $transaction->trip->users->count();
			return -$transaction->amount * (1 / $users);

		} elseif ($this->isPersonalTransaction($transaction)) {
			return -$transaction->amount;
		}

		return -$this->oweForUnevenSplit($transaction);
	}

	/**
	 * Get the amount a user is responsible for on a split transaction
	 * @param  App\Transaction
	 * @return number
	 */
	public function oweForUnevenSplit($transaction) {
		$user = $transaction->users->where('id', Auth::id());

		if ($user->isNotEmpty()) {
			return $transaction->amount
				* $user->first()->pivot->split_ratio
				/ $transaction->users->sum('pivot.split_ratio');
		}

		return 0;
	}
}
