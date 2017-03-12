<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendingEmailTrip extends Model
{
	protected $table = 'pending_email_trip';

    protected $fillable = ['email', 'trip_id'];
}
