<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trip;
use App\Library\Report\Report;
use App\Transaction;
use Auth;

class ReportController extends Controller {

    public function progress(Trip $trip) {
        $report = new Report($trip);

        return $report->generate()
			->sortBy('total')->values()->all();
    }

    public function topSpenders(Trip $trip) {
        $transactions = Transaction::where("trip_id", $trip->id)
            ->with('creator')->get()
            ->groupBy('created_by');

        $sum = $transactions->collapse()->sum('amount');

        $spend = $transactions->map(function($item) use ($sum) {
                $creator = $item->first()->creator;
                $itemSum = $item->sum('amount');

                return (object) [
                    'name' => $creator->first_name . ' ' . $creator->last_name,
                    'currency' => number_format($itemSum, 2),
                    'sum' => $itemSum,
                    'percent' => round($itemSum / $sum * 100)
                ];
            })->filter(function($item) {
                return $item->percent > 1;
            })->sortByDesc('sum')->take(5);

        return [
            'spend' => $spend->values(),
            'max' => $spend->max('sum')
        ];
    }

    public function detailed(Trip $trip) {
        $transactions = Transaction::where("trip_id", $trip->id)
            ->where(function($query) {
              $query->where('created_by', Auth::id())
                    ->orWhereHas('users', function($user) {
                        $user->where('user_id', Auth::id());
                    })->doesntHave('users', 'or');
            })
            ->with('creator', 'users')
            ->orderBy('date')
            ->get();

        //do something if this person is the only user on the trip

        return $transactions->map(function($transaction) {

            $net = $transaction->created_by === Auth::id()
                ? $this->paidByUser($transaction)
                : $this->paidByOther($transaction);

            $creator = $transaction->created_by === Auth::id()
                ? 'You'
                : $transaction->creator->first_name;

            return (object) [
                'date' => \Carbon\Carbon::parse($transaction->date)->format('m/d/Y'),
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'creator' => $creator,
                'net' => money_format('%i', $net)
            ];
        });
    }

    public function paidByUser($transaction) {

        // Even Split
        if ($transaction->users->isEmpty()) {
            $users = $transaction->trip->users->count();

            return $transaction->amount * ($users - 1) / $users;

        // Personal Transaction
        } elseif ($this->personalTransaction($transaction)) {
            return 0;
        }

        // Uneven Split
        return $transaction->amount - $this->oweForUnevenSplit($transaction);
    }

    public function paidByOther($transaction) {

        // Even Split
        if ($transaction->users->isEmpty()) {
            $users = $transaction->trip->users->count();
            return -$transaction->amount * (1 / $users);

        // Other-entered Personal Transaction
        } elseif ($this->personalTransaction($transaction)) {
            return -$transaction->amount;
        }

        // Uneven Split
        return -$this->oweForUnevenSplit($transaction);
    }

    public function oweForUnevenSplit($transaction) {
        $user = $transaction->users->where('id', Auth::id());

        if ($user->isNotEmpty()) {
            return $transaction->amount
                * $user->first()->pivot->split_ratio
                / $transaction->users->sum('pivot.split_ratio');
        }

        return 0;
    }

    public function personalTransaction($transaction) {
        return $transaction->users->count() === 1
            && $transaction->users->first()->id === Auth::id();
    }
}
