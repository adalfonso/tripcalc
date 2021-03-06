<?php namespace App\Library\Report;

use App\Transaction;
use App\Trip;
use Auth;

abstract class Report {

	protected $transactions;
	protected $trip;

	public function __construct(Trip $trip, $transactions = null) {
		$this->trip = $trip;

		if (!$transactions) {
			$this->transactions = $this->trip->transactions;
		} else {
			$this->transactions = $transactions;
		}
	}

	abstract protected function generate();

	/**
	 * Static helper to generate a report
	 * @return mixed
	 */
	public static function make(Trip $trip) {
		return (new static($trip))->generate();
	}

	/**
	 * Determine if a user is a spender on a transaction
	 * @param  object - must contain id and user type
	 * @param  App\Transaction
	 * @return boolean
	 */
	public function isSpender($user, $transaction) {
		return $transaction->allUsers
			->where('id', $user->id)
			->where('type', $user->type)
			->count() > 0;
	}

	/**
	 * Get net amount for a transaction
	 * @param  App\Transaction
	 * @param  Integer - optional
	 * @return number
	 */
	public function netTransaction($transaction, $userId = null) {
		if ($userId === null) {
			$userId = Auth::id();
		}

		return $transaction->created_by === $userId
			? $this->netIfPaidByUser($transaction)
			: $this->netIfPaidByOther($transaction);
	}

	/**
	 * Get net amount on transaction when paid by the user
	 * @param  App\Transaction
	 * @return number
	 */
	public function netIfPaidByUser($transaction) {
		if ($transaction->isEvenSplit()) {
			$users = $transaction->trip->allUsers->count();

			return $transaction->amount * ($users - 1) / $users;

		} elseif ($transaction->isPersonal()) {
			return 0;
		}

		return $transaction->amount - $this->oweForUnevenSplit($transaction);
	}

	/**
	 * Get net amount on transaction when paid by another user
	 * @param  App\Transaction
	 * @return number
	 */
	public function netIfPaidByOther($transaction) {
		if ($transaction->isEvenSplit()) {
			$users = $transaction->trip->allUsers->count();
			return - $transaction->amount * (1 / $users);

		} elseif ($transaction->isPersonal()) {
			return - $transaction->amount;
		}

		return - $this->oweForUnevenSplit($transaction);
	}

	/**
	 * Get the amount a user is responsible for on an unevenly split transaction
	 * @param  App\Transaction
	 * @return number
	 */
	public function oweForUnevenSplit($transaction) {
		$user = $transaction->allUsers
			->where('id', Auth::id())
			->where('type', 'regular');

		if ($user->isNotEmpty()) {
			return $transaction->amount
				* $user->first()->pivot->split_ratio
				/ $transaction->allUsers->sum('pivot.split_ratio');
		}

		return 0;
	}

	/**
	 * Get the split ratio for a user on a transaction
	 * @param  object - must contain id and user type
	 * @param  App\Transaction
	 * @return number
	 */
	public function splitRatio($user, $transaction) {

		if (! $this->isSpender($user, $transaction)) {
			return 0;
		}

		return $transaction->allUsers
			->where('id', $user->id)
			->where('type', $user->type)
			->first()
			->pivot
			->split_ratio;
	}

	/**
	 * Get the sum of all transactions
	 * @return number
	 */
	public function sum() {
		return $this->transactions->collapse()->sum('amount');
	}

	/**
	 * Get only the transactions the user is related to
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function userOnlyTransactions(Trip $trip = null) {
		if ($trip === null) {
			$trip = $this->trip;
		}

		return Transaction::where("trip_id", $trip->id)
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
}
