<?php namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = [
    	'trip_id', 'user_id', 'amount', 'date', 'description'
    ];

    protected $casts = [
	    'trip_id' => 'integer',
	    'user_id' => 'integer'
	];

    protected $with = ['users'];

    public function users() {
        return $this->belongsToMany('App\User', 'transaction_user')
            ->withPivot('split_ratio');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updater() {
        return $this->belongsTo('App\User', 'updated_by');
    }

    public function hashtags() {
        return $this->belongsToMany('App\Hashtag');
    }

    public function trip() {
        return $this->belongsTo('App\Trip');
    }

    /**
	 * Determine if transaction is split evenly between everyone
	 * @return boolean
	 */
    public function isEvenSplit() {
        return $this->users->isEmpty();
    }

    /**
	 * Determine if transaction a personal transaction
	 * @return boolean
	 */
	public function isPersonal() {
		return $this->users->count() === 1
			&& $this->users->first()->id === \Auth::id();
	}

    public function getDateFormatAttribute() {
        return Carbon::parse($this->date)->format('M d, Y');
    }

        /**
	 * Sum the total split ratio for the logged in user
	 * @return mixed
	 */
	public function getUserSplitAttribute() {
		$user = $this->users->where('id', \Auth::id());

        return $user->count() !== 1 ? null : $user->first()->pivot->split_ratio;
	}

    /**
	 * Get the type of transaction as a string
	 * @return string
	 */
	public function getSplitTypeAttribute() {
		if ($this->isPersonal()) {
			return 'personal';
		}

		return $this->isEvenSplit() ? 'even' : 'custom';
	}

    /**
	 * Sum the total split ratio for a transaction
	 * @return number
	 */
	public function getSplitTotalAttribute() {
		return $this->users->sum('pivot.split_ratio');
	}
}
