<?php namespace App\Library\VirtualUser;

use Carbon\Carbon;
use App\Transaction;

class Merger {

    /**
     * Create a new instance
     * @param Illuminate\Http\Request
     * @param App\Trip
     * @param App\VirtualUser
     */
    public function __construct($request, $trip, $virtualUser) {
        $this->virtualUser = $virtualUser;
        $this->user = \Auth::user();

        $this->virtualUser->load('transactions');
        $this->user->load('transactions');

        $this->rules = collect($request->rules);

        $this->mergeOptions = collect(['user', 'virtual', 'combine']);
    }

    /**
     * Determine if a merge can be performed in its current startValue
     * @return boolean
     */
    public function canMerge() {
        if ($this->hasConflicts()) {
            return $this->resolvesConflicts();
        }

        return true;
    }

    /**
     * Determine if any transactions will cause a merge conflict with the user
     * @return boolean
     */
    public function hasConflicts() {
        return $this->conflicts()->count() > 0;
    }

    /**
     * Get all conflicting transactions
     * @return Illuminate\Support\Collection
     */
    protected function conflicts() {
        return $this->virtualUser->transactions->filter(function($transaction) {
            return $this->user->transactions->contains('id', $transaction->id);
        });
    }

    /**
     * Get all conflicting transactions in augmented state
     * @return Illuminate\Support\Collection
     */
    protected function resolvables() {
        return Transaction::whereIn('id', $this->conflicts()->pluck('id'))
            ->with('users', 'virtualUsers')
            ->get()
            ->each(function($transaction) {
                $transaction->conflict = [
                    'user' => $transaction->users
                        ->where('id', \Auth::id())
                        ->first()->pivot->split_ratio,
                    'virtual' => $transaction->virtualUsers
                        ->where('id', $this->virtualUser->id)
                        ->first()->pivot->split_ratio
               ];

               $transaction->resolution = null;

               $transaction->date = Carbon::parse($transaction->date)->format('F j, Y');
            });
    }

    /**
     * Get all conflicting transactions
     * @return Illuminate\Http\Response
     */
    public function conflictResponse() {
        return response($this->resolvables(), 422);
    }


    /**
     * Determine if any merge conflicts will be resolved
     * @return boolean
     */
    public function resolvesConflicts() {
        return $this->conflicts()->filter(function($conflict) {
            return $this->rules->where('id', $conflict->id)
                ->whereIn('resolution', $this->mergeOptions)
                ->isEmpty();
        })->isEmpty();
    }

    /**
     * Merge split ratios and delete the virtual user
     * @return void
     */
    public function merge() {
        \DB::Transaction(function() {
            $this->virtualUser->transactions->each(function($transaction) {
                $split_ratio = $this->conflicts()->contains($transaction->id)
                    ? $this->resolvedSplit($transaction)
                    : $transaction->pivot->split_ratio;

                $transaction->users()->syncWithoutDetaching([
                    \Auth::id() => ['split_ratio' => $split_ratio]
                ]);

                $transaction->virtualUsers()->detach($transaction->pivot->virtual_user_id);
            });

            $this->virtualUser->delete();
        });
    }

    /**
     * Get the split ratio needed to resolve a transaction merge
     * @param App\Transaction;
     * @return float
     */
    public function resolvedSplit($transaction) {
        $rule = $this->rules->where('id', $transaction->id)->first();
        $resolution = $rule['resolution'];
        $conflict = $rule['conflict'];

        if ($resolution === 'combine') {
            return floatval($conflict['user']) + floatval($conflict['virtual']);
        }

        return floatval($conflict[$resolution]);
    }
}
