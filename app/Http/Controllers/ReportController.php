<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trip;
use App\Library\Report\Report;
use App\Transaction;

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
}
