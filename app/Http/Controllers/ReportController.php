<?php namespace App\Http\Controllers;

use App\Trip;
use App\Library\Report\BottomLineReport;
use App\Library\Report\DetailedReport;
use App\Library\Report\DistributionReport;
use App\Library\Report\TopSpendersReport;


class ReportController extends Controller {

    public function bottomLine(Trip $trip) {
        $report = new BottomLineReport($trip);

        return $report->generate();
    }

    public function distribution(Trip $trip) {
        $report = new DistributionReport($trip);

        return $report->generate();
    }

    public function topSpenders(Trip $trip) {
        $report = new TopSpendersReport($trip);

        return $report->generate();
    }

    public function detailed(Trip $trip) {
        $report = new DetailedReport($trip);

        return $report->generate();
    }
}
