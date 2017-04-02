<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model {

	protected $fillable = ['tag'];

	public $timestamps = false;

	public function transactions() {
		return $this->belongsToMany('App\Transaction');
	}
}