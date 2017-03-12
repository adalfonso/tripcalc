<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\TripUser;
use App\PendingEmailTrip;

class TransitionTripRequests
{
    public $user;

    /**
     * @return void
     */
    public function __construct() {
    }

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(Registered $event) {
        $this->user = $event->user;

        $trips = PendingEmailTrip::where(
            'email', $this->user->email
        )->get();

        $trips->each(function($trip) {
            $this->addTripToAccount($trip);
        });

        PendingEmailTrip::destroy(
            $trips->pluck('id')->toArray()
        );
    }

    /**
     * Move a pending trip to a user's account
     *
     * @param  PendingEmailTrip $trip
     * @return void
     */
    public function addTripToAccount(PendingEmailTrip $trip) {
        TripUser::create([
            'user_id' => $this->user->id,
            'trip_id' => $trip->trip_id,
            'active'  => 0
        ]);
    }
}
