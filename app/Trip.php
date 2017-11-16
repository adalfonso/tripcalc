<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Library\Notification\Notifier;
use Carbon\Carbon;
use Auth;

class Trip extends Model {

	use Notifier;

	protected $fillable = [
		'name',
		'start_date',
		'end_date',
	    'budget',
	    'description',
		'virtual_users'
	];

	public static function boot() {
		static::deleting(function($trip) {
			$trip->notifications()->delete();
			$trip->posts()->delete();
		});
	}


	public function posts() {
		return $this->morphMany('App\Post', 'postable');
	}

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

	public function notifications() {
		return $this->morphMany('App\Notification', 'notifiable');
	}

	public function allUserSettings() {
		return $this->hasMany('App\TripUserSetting');
	}

	public function userSettings() {
		return $this->hasOne('App\TripUserSetting')
			->where('user_id', Auth::id());
	}

	public function getIsClosedOutAttribute() {
		$settings = $this->allUserSettings;
		$closeouts = $settings->where('closeout', true);

		return $settings->count() === $closeouts->count();
	}

	public function getCloseoutPendingAttribute() {
		$settings = $this->allUserSettings;
		$closeouts = $settings->where('closeout', true);

		return $settings->count() > $closeouts->count()
		    && $closeouts->count() > 0;
	}

	public function getOthersAttribute() {
        return $this
            ->users()
            ->where('id', '!=', Auth::id())
            ->get();
    }

	public function getStateAttribute() {
		if (!$this->active) {
			return 'closed';
		}

		return $this->closeoutPending ? 'closing' : 'active';
	}

	public function virtualUsers() {
		return $this->hasMany('\App\VirtualUser');
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

	public function getAllUsersAttribute() {
		return $this->users->merge($this->virtualUsers)
			->each(function($user) {
				$user->type = get_class($user) === 'App\VirtualUser'
				? 'virtual'
				: 'regular';
			});
	}
}
