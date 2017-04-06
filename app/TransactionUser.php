<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use \App\User;

class TransactionUser extends Model {

    protected $table = 'transaction_user';

    protected $fillable = ['transaction_id', 'user_id', 'split_ratio'];

    public function user() {
    	return $this->belongsTo('\App\User')
    	->select('id', 'first_name', 'last_name');
    }
}
