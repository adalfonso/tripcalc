<?php namespace App\Library\Report;

trait AllUsers {

    protected $originalTotal;
    protected $extraAmount;

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
     * Get just the debtors
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function debtors() {
        return $this->report->where('total', '>', 0);
    }

    /**
     * Get just the spenders
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function spenders() {
        return $this->report->where('total', '<', 0);
    }

    /**
     * Set each user's net total
     * @return void
     */
    public function netBalanceAllUsers() {
        $this->transactions->each(function($transaction) {

			// If user entered the transaction it is subtracted from their total
            $user = $this->report->where('id', $transaction->created_by)->first();
            $user->total = bcsub($user->total, $transaction->amount, 2);

			if ($transaction->isEvenSplit()) {
				return $this->splitEvenly($transaction);
			}

			return $this->splitUnevenly($transaction);
		});

        $this->migrateFractionalCents();
    }

    /**
     * Handle fractional cents for net totals
     * @return void
     */
    public function migrateFractionalCents() {

        // Set original total
        $this->originalTotal = $this->absoluteTotal();

        // Remove fractional cents from each speder's net total
		$this->report->each(function($spender) {
            $this->trimCents($spender);
		});

        // Set extra amount to account for
        $this->extraAmount = bcmul(
            round($this->report->sum('total') * 100), 1, 2
        );

        // Apply extra amount to users
        $this->applyAllExtras();
	}

    /**
     * Facilitates extras being applied
     * @return void
     */
    public function applyAllExtras() {
        $targetGroup = $this->extraAmount > 0
            ? $this->spenders()
            : $this->debtors();

        while (! $this->extraApplied()) {
            $targetGroup->sortByDesc('fractionalModifier')
                ->each(function($user) {
                    if ($this->extraApplied()) {
                        return false;
                    }

                    $this->applyExtra($user);
                });
        }
    }

    /**
     * Apply a leftover cent to a user
     * @return void
     */
    public function applyExtra($user) {
        if ($this->extraAmount > 0) {
            $this->extraAmount--;
        } else {
            $this->extraAmount++;
        }

        // Debtors
        if ($user->total > 0) {
            return $user->total = bcadd($user->total, .01, 2);
        }

        // Spenders
        return $user->total = bcsub($user->total, .01, 2);
    }

    /**
     * Checks if all extra cents have been applied
     * @return boolean
     */
    public function extraApplied() {
        return bccomp($this->extraAmount, 0) === 0;
    }

    /**
     * Get absolute-valued total for all user's net totals
     * @return string
     */
    public function absoluteTotal() {
        $exactTotal = $this->report->sum(function($spender) {
            return abs($spender->total);
        });

        $roundedDown = bcmul($exactTotal, 1, 2);

        if ($roundedDown < $exactTotal) {
            return bcadd($roundedDown, .01, 2);
        }

        return $roundedDown;
    }

    /**
     * Trim off fractional cents
     * @return void
     */
    public function trimCents($spender) {
        $newTotal = bcmul($spender->total, 1, 2);

        $fractionalModifier = bcmul(
            abs(bcsub($spender->total, $newTotal, 6)),
            10000,
            2);

        $spender->total = $newTotal;
        $spender->fractionalModifier = $fractionalModifier;
    }

    /**
     * Adjust totals for an evenly split transaction
     * @param  App\Transaction
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function splitEvenly($transaction) {
        return $this->report->each(function($user) use($transaction) {
            $user->total += $transaction->amount / $this->report->count();
        });
    }

    /**
     * Adjust totals for an unevenly split transaction
     * @param  App\Transaction
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function splitUnevenly($transaction) {
        return $this->report->each(function($user) use($transaction) {
            $splitPercent =
                $this->splitRatio($user->id, $transaction) /
                $transaction->splitTotal;

            $user->total += $transaction->amount * $splitPercent;
        });
    }
}
