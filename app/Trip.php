<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;

class Trip extends Model {

	protected $fillable = [
		'name',
		'start_date',
		'end_date',
	    'budget',
	    'description'
	];

	public function transactions() {
        return $this->hasMany('\App\Transaction');
    }

	public function users() {
		return $this->belongsToMany('\App\User', 'trip_user')

					// Activated Account
					->where('activated', true)

					// Active On Trip
					->where('active', true);
	}

	public function getDateRangeAttribute() {
		$start = Carbon::parse($this->start_date);
		$end = Carbon::parse($this->end_date);

		if ($start->diffInDays($end) == 0) {
			return $start->format('F j, Y');

		} else if ($start->diffInMonths($end) == 0) {
			return $start->format('F j') . ' - ' . $end->format('j, Y');

		} else if ($start->year == $end->year) {
			return $start->format('F j') . ' - ' . $end->format('F j, Y');
		}

		return $start->format('F j, Y') . ' - ' . $end->format('F j, Y');
	}
}
