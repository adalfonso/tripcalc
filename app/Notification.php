<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {

    protected $fillable = [
        'user_id', 'subtype', 'seen', 'created_at', 'created_by'
    ];

    public static function boot() {
        parent::boot();

        static::creating(function (Notification $notification) {
            $notification->created_by = \Auth::id();
            $notification->updated_by = \Auth::id();
        });

        static::updating(function (Notification $notification) {
            $notification->updated_by = \Auth::id();
        });
    }

    public function notifiable() {
        return $this->morphTo();
    }

    public function creator() {
        return $this->belongsTo('App\User', 'created_by');
    }
}
