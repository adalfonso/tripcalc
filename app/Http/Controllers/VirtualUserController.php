<?php namespace App\Http\Controllers;

use App\Transaction;
use App\Trip;
use App\VirtualUser;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VirtualUserController extends Controller {

    public function index(Trip $trip) {
        return $trip->virtualUsers()->with('transactions')
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'deleteable' => $user->transactions->isEmpty()
                ];
            });
    }

    public function store(Request $request, Trip $trip) {
        $names = $trip->virtualUsers->pluck('name')->implode(',');

        $this->validate($request, [
            'name' => 'required|max:63|notIn:' . $names
        ]);

        $user = new VirtualUser;
        $user->name = $request->name;
        $user->trip_id = $trip->id;
        $user->save();

        return $this->index($trip);
    }

    public function update(Request $request, Trip $trip, VirtualUser $virtualUser) {
        $names = $trip->virtualUsers->pluck('name')->implode(',');

        $this->validate($request, [
            'name' => 'required|max:63|notIn:' . $names
        ]);

        $virtualUser->name = $request->name;
        $virtualUser->save();

        return $this->index($trip);
    }

    public function destroy(Trip $trip, VirtualUser $virtualUser) {
        if ($virtualUser->transactions->count() > 0) {
            return;
            // some error
        }

        $virtualUser->delete();

        return $this->index($trip);
    }

    public function attemptMerge(Request $request, Trip $trip, VirtualUser $virtualUser) {
        $transactions = Auth::user()->transactions()
            ->where('trip_id', $trip->id)->get();

        $this->conflicts = $virtualUser->transactions
            ->filter(function($transaction) use ($transactions) {
                return $transactions->contains('id', $transaction->id);
            })->pluck('id');

        $this->rules = collect($request->rules);

        if (! $this->resolvesConflicts()) {
            return response($this->conflicts($virtualUser), 422);
        }

        \DB::Transaction(function() use ($virtualUser) {
            $virtualUser->transactions->each(function($transaction) {
                $split_ratio = $this->conflicts->contains($transaction->id)

                ? $this->resolvedSplit(
                    $transaction, $this->rules->where(
                        'id', $transaction->id
                    )->first()
                )

                : $transaction->pivot->split_ratio;

                $transaction->users()->syncWithoutDetaching([
                    Auth::id() => ['split_ratio' => $split_ratio]
                ]);

                $transaction->virtualUsers()->detach($transaction->pivot->virtual_user_id);
            });

            $virtualUser->delete();
        });
    }

    public function resolvedSplit($transaction, $rule) {
        $resolution = $rule['resolution'];
        $conflict = $rule['conflict'];

        if ($resolution === 'combine') {
            return floatval($conflict['user']) + floatval($conflict['virtual']);
        }

        return floatval($conflict[$resolution]);
    }

    protected function resolvesConflicts() {
        return $this->conflicts->filter(function($conflict) {
            $options =  ['user', 'virtual', 'combine'];

            return $this->rules->where('id', $conflict)
                ->whereIn('resolution', $options)
                ->isEmpty();
        })
        ->isEmpty();
    }

    protected function conflicts($virtualUser) {
        return Transaction::whereIn('id', $this->conflicts)
            ->with('users', 'virtualUsers')
            ->get()
            ->each(function($transaction) use ($virtualUser) {
                $transaction->conflict = [
                    'user' => $transaction->users
                        ->where('id', Auth::id())
                        ->first()->pivot->split_ratio,
                    'virtual' => $transaction->virtualUsers
                        ->where('id', $virtualUser->id)
                        ->first()->pivot->split_ratio
               ];

               $transaction->resolution = null;

               $transaction->date = Carbon::parse($transaction->date)->format('F j, Y');
            });
    }
}
