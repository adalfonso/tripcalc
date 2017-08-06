<?php namespace App;

use App\Library\Notification\Notifier;
use App\Mail\PasswordReset;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Mail;

class User extends Authenticatable {

 // use CanResetPassword;
    use Notifier;
    use Notifiable;

    protected $fillable = [
        'first_name', 'last_name',
        'username', 'email',
        'about', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    // Relationships
    public function friendshipAsRequester() {
        return $this->belongsToMany(
            '\App\User', 'friends', 'requester_id', 'recipient_id'
        )
        ->wherePivot('confirmed', 1)
        ->withPivot('confirmed');
    }

    public function friendshipAsRecipient() {
        return $this->belongsToMany(
         '\App\User', 'friends', 'recipient_id', 'requester_id'
        )
        ->wherePivot('confirmed', 1)
        ->withPivot('confirmed');
    }

    public function pendingFriendRequests() {
        return $this->belongsToMany(
         '\App\User', 'friends', 'recipient_id', 'requester_id'
        )
        ->where('confirmed', 0)
        ->select('users.id', 'users.first_name', 'users.last_name');
    }

    public function activation() {
        return $this->hasOne('App\UserActivation');
    }

    public function notifications() {
        return $this->hasMany('App\Notification');
    }

    public function notificationsAsModel() {
        return $this->morphMany('App\Notification', 'notifiable');
    }

    public function getUnseenNotificationsAttribute() {
        return $this->hasMany('App\Notification')
            ->where('seen', false)
            ->get();
    }

    public function trips() {
        return $this->belongsToMany('\App\Trip');
    }

    public function activeTrips() {
        return $this->trips()->wherePivot('active', true);
    }

    public function pendingTripRequests() {
        return $this->trips()->wherePivot('active', false);
    }

    public function transactions() {
        return $this->belongsToMany('App\Transaction');
    }

    public function profilePosts() {
		return $this->morphMany('App\Post', 'postable');
	}

    public function isCurrentUser() {
        return $this->id === Auth::id();
    }

    public function recentProfilePosts($date = null) {
        $comparison = is_null($date) ? '<=' : '<';
        $date = is_null($date) ? Carbon::now() : Carbon::parse($date);

        return $this->profilePosts->where('created_at', $comparison, $date)
            ->sortByDesc('created_at')->take(15)
            ->map(function($item) {
                return (object) [
                    'type' => 'post',
                    'id' => $item->id,
                    'poster' => $item->user->fullname,
                    'created_at' => $item->created_at,
                    'editable' => $item->created_by === \Auth::id(),
                    'content' => $item->content,
                    'dateForHumans' => $item->created_at->diffForHumans()
                ];
            })->values();
    }

    public function getAllRequestsAttribute() {
        return collect([
            'friend' => $this->pendingFriendRequests,
            'trip' =>  $this->pendingTripRequests
        ]);
    }

    // Accessors
    public function getCurrentPhotoAttribute() {
		return $this->morphMany('App\Photo', 'related')
            ->orderBy('created_at', 'desc')
            ->first();
	}

    public function getCurrentThumbnailAttribute() {
		$photo = $this->currentPhoto;

        if (! $photo) {
            return null;
        }

        return preg_replace('/.jpeg/', '-thumb.jpeg', $photo->path);
	}

    public function getFriendsAttribute() {
        $this->setRelation('friends', $this->mergeFriends());

        return $this->getRelation('friends');
    }

    public function getFullNameAttribute() {
        return $this->first_name . " " . $this->last_name;
    }

    protected function mergeFriends() {
        return $this
            ->friendshipAsRequester
            ->merge($this->friendshipAsRecipient);
    }

    public function getTypeAttribute() {
        return 'regular';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
        Mail::to($this)->send(new PasswordReset($token));
        //$this->notify(new ResetPasswordNotification($token));
    }
}
