<?php namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {

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

    public function pendingTripRequests() {
        return $this->trips()->where('trip_user.active', 0);
    }

    public function trips() {
        return $this->belongsToMany('\App\Trip');
    }

    public function activeTrips() {
        return $this->belongsToMany('\App\Trip')->wherePivot('active', true);
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

}
