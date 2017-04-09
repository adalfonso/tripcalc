<?php namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Mail\PasswordReset;
use Mail;

class User extends Authenticatable {

   // use CanResetPassword;
    use Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'password',
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

    public function trips() {
        return $this->belongsToMany('\App\Trip');
    }

    public function activeTrips() {
        return $this->trips()->wherePivot('active', true);
    }

    public function pendingTripRequests() {
        return $this->trips()->wherePivot('active', false);
    }


    // Accessors
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