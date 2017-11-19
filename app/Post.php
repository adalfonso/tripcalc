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

        static::creating(function($post) {
            $post->created_by = Auth::id();
            $post->updated_by = Auth::id();
        });

        static::updating(function($post) {
            $post->updated_by = Auth::id();
        });

        static::deleting(function($post) {
            $post->notifications()->delete();
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
        $this->load('user', 'comments.user', 'postable');
        $users = $this->comments->pluck('user')->push($this->user);

        if (class_basename($this->postable) === 'User') {
            $users->push($this->postable);
        }

        return $users->unique();
    }

    public function getOthersAttribute() {
        return $this->users->whereNotIn('id', [Auth::id()]);
    }

    public function getDiffForHumansAttribute() {
        return $this->created_at->diffForHumans();
    }
}
