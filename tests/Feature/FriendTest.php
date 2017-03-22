<?php namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Auth;

use App\Friend;
use App\Trip;
use App\User;

class FriendTest extends DuskTestCase {

    use DatabaseTransactions;
    use WithoutMiddleware;

    public function setUp(){
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }

    public function generateAndCreateUser() {
        return User::create([
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'username'   => $this->faker->userName,
            'email'      => $this->faker->email,
            'password'   => bcrypt('password')
        ]);
    }

    public function generateAndCreateTrip() {
        return Trip::create([
            'name'       => $this->faker->company,
            'budget'     => $this->faker->numberBetween(0, 1000),
            'start_date' => $this->faker->dateTime(),
            'end_date'   => $this->faker->dateTime()
        ]);
    }

    public function createActiveFriendship(User $requester, User $recipient) {
        return Friend::create([
            'requester_id' => $requester->id,
            'recipient_id' => $recipient->id,
            'confirmed'    => 1
        ]);
    }

    /** @test */
    public function people_search_returns_max_of_5_results() {

        $response = $this->post('/users/search', ['input' => 'h']);

        $count = sizeof($response->json());

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function people_search_finds_a_specific_user() {

        $response = $this->post('/users/search', ['input' => 'hershey']);

        $this->assertEquals('somethingcool1', $response->json()[0]['username']);
    }

    /** @test */
    public function a_user_can_invite_a_friend_to_their_trip() {

        $user1 = $this->generateAndCreateUser();
        $user2 = $this->generateAndCreateUser();
        $this->createActiveFriendship($user1, $user2);

        $trip = $this->generateAndCreateTrip();
        $trip->users()->attach($user1->id, ['active' => 1]);

        Auth::loginUsingId($user1->id);

        $response = $this->post(
            "/trips/$trip->id/searchEligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(1, sizeof($response->json()));
    }

    /** @test */
    public function a_user_cant_invite_a_non_friend_to_their_trip() {

        $user1 = $this->generateAndCreateUser();
        $user2 = $this->generateAndCreateUser();

        $trip = $this->generateAndCreateTrip();
        $trip->users()->attach($user1->id, ['active' => 1]);

        Auth::loginUsingId($user1->id);

        $response = $this->post(
            "/trips/$trip->id/searchEligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(0, sizeof($response->json()));
    }
}
