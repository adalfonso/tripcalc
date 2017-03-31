<?php namespace Tests\Library;

use Auth;

use App\Friend;
use App\Transaction;
use App\Trip;
use App\User;

class Maker {

    public function __construct() {
        $this->faker = \Faker\Factory::create();
    }

    public function user() {
        $user = User::create([
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'username'   => $this->faker->userName,
            'email'      => $this->faker->email,
            'password'   => bcrypt('password')
        ]);

        $user->activated = 1;
        $user->save();

        return $user;
    }

    public function transaction($trip, $user) {
        return Transaction::create([
            'trip_id' => $trip->id,
            'amount' => $this->faker->numberBetween(0, 1000),
            'date' => $this->faker->dateTime(),
            'description' => $this->faker->sentence(),
            'hashtags' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);
    }

    public function trip() {
        return Trip::create([
            'name'       => $this->faker->company,
            'budget'     => $this->faker->numberBetween(0, 1000),
            'start_date' => $this->faker->dateTime(),
            'end_date'   => $this->faker->dateTime()
        ]);
    }

   	public function attachTripUser(Trip $trip, User $user) {
   		$trip->users()->attach($user->id, ['active' => 1]);
   	}

    public function activeFriendship(User $requester, User $recipient) {
        return Friend::create([
            'requester_id' => $requester->id,
            'recipient_id' => $recipient->id,
            'confirmed'    => 1
        ]);
    }

    public function login($user) {
    	Auth::loginUsingId($user->id);
    }
}
