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
		return $this->belongsToMany('\App\User', 'trip_user');
	}

	public function getDateRangeAttribute() {
		$start = Carbon::parse($this->start_date);
		$end = Carbon::parse($this->end_date);

		if ($start->diffInDays($end) == 0) {
			return $start->format('F d, Y');

		} else if ($start->diffInMonths($end) == 0) {
			return $start->format('F d') . ' - ' . $end->format('d, Y');

		} else if ($start->year == $end->year) {
			return $start->format('F d') . ' - ' . $end->format('F d, Y');
		}

		return $start->format('F d, Y') . ' - ' . $end->format('F d, Y');
	}
}
