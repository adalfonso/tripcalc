<?php namespace App\Http\Controllers;

use App\Trip;
use Auth;
use App\Library\Report\BottomLineReport;
use App\Library\Report\CloseoutReport;
use App\Library\Report\DetailedReport;
use App\Library\Report\DistributionReport;
use App\Library\Report\TopSpendersReport;

class ReportController extends Controller {

    public function bottomLine(Trip $trip) {
        $user = CloseoutReport::make($trip)['spenders']
            ->where('id', Auth::id())->first();

        if (sizeof($user->credits) > 0) {
            return collect($user->credits)->sum();
        }

        return - collect($user->debits)->sum();
    }

    public function closeout(Trip $trip) {
        return CloseoutReport::make($trip);
    }

    public function distribution(Trip $trip) {
        return DistributionReport::make($trip);
    }

    public function topSpenders(Trip $trip) {
        return TopSpendersReport::make($trip);
    }

    public function detailed(Trip $trip) {
        $transactions = DetailedReport::make($trip);

        $multiUser = $transactions->unique('creatorId')->count() > 1
            || $trip->users->count() > 1;

        return [
            'transactions' => $transactions,
            'multiUser' => $multiUser
        ];
    }

    public function extended(Trip $trip) {
        $transactions = DetailedReport::make($trip);

        $total = $transactions->where('isCreator', true)->sum('amount');
        $netTotal = $transactions->sum('net');        

        return view('report.extended', compact('transactions', 'total', 'netTotal'));
    }
}
