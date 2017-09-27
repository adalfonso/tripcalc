<?php namespace App;

use App\Library\Notification\Notifier;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    use Notifier;

    protected $fillable = ['content', 'parent_id'];

    public static function boot() {
        parent::boot();

        static::saving(function($table) {
            $table->created_by = Auth::id();
            $table->updated_by = Auth::id();
        });

        static::updating(function($table) {
            $table->updated_by = Auth::id();
        });
    }


    public function comments() {
        return $this->hasMany('App\Comment');
    }

    public function notifications() {
		return $this->morphMany('App\Notification', 'notifiable');
	}

    public function postable() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo('App\User', 'created_by')
            ->select('id', 'first_name', 'last_name');
    }
    

    public function getUsersAttribute() {
        $this->load('user', 'comments.user');

        return $this->comments->pluck('user')
            ->push($this->user)->unique();
    }

    public function getOthersAttribute() {
        return $this->users->whereNotIn('id', [Auth::id()]);
    }

    public function getDiffForHumansAttribute() {
        return $this->created_at->diffForHumans();
    }
}
