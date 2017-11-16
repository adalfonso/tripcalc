<?php namespace Tests\Library;

use Auth;

use App\Friend;
use App\Post;
use App\Hashtag;
use App\Transaction;
use App\Trip;
use App\User;
use App\VirtualUser;


class Maker {

    public function __construct() {
        $this->faker = \Faker\Factory::create();
    }

    public function hashtag() {
        return Hashtag::firstOrCreate(['tag' => $this->faker->word]);
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

    public function virtualUser(Trip $trip) {

        $user = new VirtualUser;
        $user->name = $this->faker->firstName;
        $user->trip_id = $trip->id;

        $user->save();

        return $user;
    }

    public function post($postable, $type, $user) {
        $post = new Post([
            'date' => $this->faker->dateTime(),
            'content' => $this->faker->sentence()
        ]);

        $post->postable_id =  $postable->id;
        $post->postable_type = 'App\\' . ucfirst($type);
        $post->created_by = $user->id;
        $post->save();

        return $post;
    }

    public function transaction($trip, $user, $amount = null) {
        $transaction = new Transaction([
            'trip_id' => $trip->id,
            'amount' => $amount != null ? $amount : $this->faker->numberBetween(0, 1000),
            'date' => $this->faker->dateTime(),
            'description' => $this->faker->word
        ]);

        $transaction->created_by = $user->id;
        $transaction->updated_by = $user->id;
        $transaction->save();

        $transaction->hashtags()->sync(
            [$this->hashtag()->id, $this->hashtag()->id]
        );

        return $transaction;
    }

    public function trip() {
        // Needs logged in user to create trip
        if (!Auth::id()) {
            Auth::loginUsingId(57);
        }

        return Trip::create([
            'name'       => $this->faker->company,
            'budget'     => $this->faker->numberBetween(0, 1000),
            'start_date' => $this->faker->dateTime(),
            'end_date'   => $this->faker->dateTime()
        ]);
    }

    public function attachTransactionUser(Transaction $transaction, User $user, $split) {
        $transaction->users()->attach($user
            ->id, ['split_ratio' => $split]);
    }

    public function attachTransactionVirtualUser(Transaction $transaction, VirtualUser $user, $split) {
        $transaction->virtualUsers()->attach($user
            ->id, ['split_ratio' => $split]);
    }

   	public function attachTripUser(Trip $trip, User $user) {
   		$trip->users()->attach($user->id, ['active' => 1]);
   	}

    public function attachInactiveTripUser(Trip $trip, User $user) {
        $trip->users()->attach($user->id, ['active' => 0]);
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
