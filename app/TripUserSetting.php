<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TripUserSetting extends Model {

    protected $fillable = [
        'trip_id', 'user_id', 'private_transactions', 'editable_transactions'
    ];
}
