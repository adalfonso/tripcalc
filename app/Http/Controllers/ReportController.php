<?php namespace App\Http\Controllers;

use App\Trip;
use App\Library\Report\DetailedReport;
use App\Library\Report\ProgressReport;
use App\Library\Report\TopSpendersReport;


class ReportController extends Controller {

    public function progress(Trip $trip) {
        $report = new ProgressReport($trip);

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
