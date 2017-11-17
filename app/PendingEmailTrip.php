<?php namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class PendingEmailTrip extends Model {

	protected $table = 'pending_email_trip';

    protected $fillable = ['email', 'trip_id'];

	public static function boot() {
        parent::boot();

        static::creating(function($pendingTripEmail) {
            $pendingTripEmail->created_by = Auth::id();
            $pendingTripEmail->updated_by = Auth::id();
        });

        static::updating(function($pendingTripEmail) {
			$pendingTripEmail->updated_by = Auth::id();
        });
    }
}
