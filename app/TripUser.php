<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TripUser extends Model
{
	protected $table = 'trip_user';

	protected $fillable = ['user_id', 'trip_id', 'active'];
}
