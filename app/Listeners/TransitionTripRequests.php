<?php namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\PendingEmailTrip;
use DB;

class TransitionTripRequests {

    public $user;

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(Registered $event) {
        $this->user = $event->user;

        DB::transaction(function(){
            $pendingTrips = PendingEmailTrip::where(
                'email', $this->user->email
            )->get();

            $trips = $pendingTrips->keyBy('trip_id')->map(function($item) {
                return ['active' => false];
            });

            User::find($this->user->id)->trips()
                ->attach($trips);

            // $trips->each(function($trip) {
            //     $this->addTripToAccount($trip);
            // });

            PendingEmailTrip::destroy(
                $pendingTrips->pluck('id')->toArray()
            );
        });
    }
}
