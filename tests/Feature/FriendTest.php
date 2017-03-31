<?php namespace Tests\Browser;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FriendTest extends DuskTestCase {

    use DatabaseTransactions;
    use WithoutMiddleware;

    public function setUp(){
        parent::setUp();
        $this->maker = new Maker;
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
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $this->maker->activeFriendship($user1, $user2);
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trips/$trip->id/searchEligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(1, sizeof($response->json()));
    }

    /** @test */
    public function a_user_cant_invite_a_non_friend_to_their_trip() {
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trips/$trip->id/searchEligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(0, sizeof($response->json()));
    }
}
