<?php namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = [
    	'trip_id', 'user_id', 'amount', 'date', 'description', 'created_by', 'updated_by'
    ];

    protected $casts = [
	    'trip_id' => 'integer',
	    'user_id' => 'integer'
	];

    public function spenders() {
    	return $this->belongsToMany(
    		'App\User', 'transaction_user', 'transaction_id', 'user_id'
    	)->select('transaction_user.id as pivot_id','users.id', 'transaction_user.split_ratio');
    }

    public function users() {
        return $this->belongsToMany('App\User', 'transaction_user');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function hashtags() {
        return $this->belongsToMany('App\Hashtag');
    }

    public function getDateFormatAttribute() {
        return Carbon::parse($this->date)->format('M d, Y');
    }
}
