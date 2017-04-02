<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

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
    		'\App\User', 'transaction_user', 'transaction_id', 'user_id'
    	)->select('transaction_user.id as pivot_id','users.id', 'transaction_user.split_ratio');
    }

    public function users() {
        return $this->belongsToMany('\App\User', 'transaction_user');
    }

    public function hashtags() {
        return $this->belongsToMany('App\Hashtag');
    }
}
