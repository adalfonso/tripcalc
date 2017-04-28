<?php namespace App\Library\Report;

use App\Transaction;
use App\Trip;
use Auth;

abstract class Report {

	protected $report;
	protected $transactions;
	protected $trip;

	public function __construct(Trip $trip) {
		$this->trip = $trip;
		$this->transactions = $this->trip->transactions;
	}

	abstract public function generate();

	/**
	 * Get all possible users associated with trip, spending, and splits
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function allUsers() {
		$usersFromSpend = $this->transactions->pluck('creator');

		$usersFromSplit = $this->transactions->pluck('users')->collapse();

		return $this->trip->users
			->merge($usersFromSpend)
			->merge($usersFromSplit)
		    ->unique('id');
	}

	/**
	 * Determine if a user_id is a spender on a transaction
	 * @param  integer
	 * @param  App\Transaction
	 * @return boolean
	 */
	public function isSpender($id, $transaction) {
		return $transaction->users->where('id', $id)->count() > 0;
	}

	/**
	 * Determine if transaction is split evenly between everyone
	 * @param  App\Transaction
	 * @return boolean
	 */
	public function isEvenSplit($transaction) {
		return $transaction->users->isEmpty();
	}

	/**
	 * Determine if transaction a personal transaction
	 * @param  App\Transaction
	 * @return boolean
	 */
	public function isPersonalTransaction($transaction) {
		return $transaction->users->count() === 1
			&& $transaction->users->first()->id === Auth::id();
	}

	/**
	 * Get net transaction amount on transaction paid by the user
	 * @param  App\Transaction
	 * @return number
	 */
	public function netIfPaidByUser($transaction) {
		if ($this->isEvenSplit($transaction)) {
			$users = $transaction->trip->users->count();

			return $transaction->amount * ($users - 1) / $users;

		} elseif ($this->isPersonalTransaction($transaction)) {
			return 0;
		}

		return $transaction->amount - $this->oweForUnevenSplit($transaction);
	}

	/**
	 * Get net transaction amount on transaction paid by another user
	 * @param  App\Transaction
	 * @return number
	 */
	public function netIfPaidByOther($transaction) {
		if ($this->isEvenSplit($transaction)) {
			$users = $transaction->trip->users->count();
			return - $transaction->amount * (1 / $users);

		} elseif ($this->isPersonalTransaction($transaction)) {
			return - $transaction->amount;
		}

		return - $this->oweForUnevenSplit($transaction);
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

	/**
	 * Sum the total split ratios for a transaction
	 * @param  App\Transaction
	 * @return number
	 */
	public function splitTotal($transaction) {
		return $transaction->users->sum('pivot.split_ratio');
	}

	/**
	 * Get the split ratio for a user_id on a transaction
	 * @param  integer
	 * @param  App\Transaction
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

	/**
	 * Get the sum of all transactions
	 * @return number
	 */
	public function sum() {
		return $this->transactions->collapse()->sum('amount');
	}

	/**
	 * Get net amount for a transaction
	 * @param  App\Transaction
	 * @return number
	 */
	public function transactionNet($transaction) {
		return $transaction->created_by === Auth::id()
			? $this->netIfPaidByUser($transaction)
			: $this->netIfPaidByOther($transaction);
	}

	/**
	 * Get only the transactions the user is related to
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function userOnlyTransactions() {
		return Transaction::where("trip_id", $this->trip->id)
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
