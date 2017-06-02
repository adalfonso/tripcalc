<?php namespace Tests\Browser;

use App\Mail\AccountInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;

class FriendTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        Mail::fake();
        $this->maker = new Maker;
    }

    /** @test */
    public function people_search_returns_max_of_5_results() {
        $this->withoutMiddleware();
        $response = $this->post('/user/search', ['input' => 'h']);

        $count = sizeof($response->json());

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function people_search_finds_a_specific_user() {
        $this->withoutMiddleware();
        $response = $this->post('/user/search', ['input' => 'hershey']);

        $this->assertEquals('somethingcool1', $response->json()[0]['username']);
    }

    /** @test */
    public function a_user_can_add_a_friend() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $this->maker->login($user1);

        $response = $this->post('/friend/' . $user2->id . '/request', [
            '_token' => csrf_token()
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('friends', [
            'requester_id' => $user1->id,
            'recipient_id' => $user2->id,
            'confirmed'    => 0
        ]);
    }

    /** @test */
    public function a_user_can_accept_a_friend_request() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();

        $this->maker->login($user1);
        $response = $this->post('/friend/' . $user2->id . '/request', [
            '_token' => csrf_token()
        ]);
        $response->assertStatus(200);

        $this->maker->login($user2);

        $response = $this->post('/friend/' . $user1->id . '/resolveRequest', [
            '_token' => csrf_token(),
            'resolution' => 1
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('friends', [
            'requester_id' => $user1->id,
            'recipient_id' => $user2->id,
            'confirmed'    => 1
        ]);
    }

    /** @test */
    public function a_user_can_reject_a_friend_request() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();

        $this->maker->login($user1);
        $response = $this->post('/friend/' . $user2->id . '/request', [
            '_token' => csrf_token()
        ]);
        $response->assertStatus(200);

        $this->maker->login($user2);

        $response = $this->post('/friend/' . $user1->id . '/resolveRequest', [
            '_token' => csrf_token(),
            'resolution' => -1
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('friends', [
            'requester_id' => $user1->id,
            'recipient_id' => $user2->id,
            'confirmed'    => -1
        ]);
    }

    /** @test */
    public function a_user_can_unfriend_a_friend() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $this->maker->activeFriendship($user1, $user2);

        $friendship = \App\Friend::where([
            'requester_id' => $user1->id,
            'recipient_id' => $user2->id,
            'confirmed'    => 1
        ])->get();

        $this->assertEquals(1, $friendship->count());

        $this->maker->login($user1);
        $response = $this->delete('/friend/' . $user2->id . '/unfriend', [
            '_token' => csrf_token()
        ]);

        $response->assertStatus(200);

        $friendship = \App\Friend::where([
            'requester_id' => $user1->id,
            'recipient_id' => $user2->id,
            'confirmed'    => 1
        ])->get();

        $this->assertEquals(0, $friendship->count());
    }

    /** @test */
    public function a_user_can_search_an_eligible_friend_for_their_trip() {
        $this->withoutMiddleware();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $this->maker->activeFriendship($user1, $user2);
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/eligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(1, sizeof($response->json()));
    }

    /** @test */
    public function a_user_cant_search_a_non_friend_for_their_trip() {
        $this->withoutMiddleware();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/eligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(0, sizeof($response->json()));
    }

    /** @test */
    public function a_user_cant_search_a_friend_already_on_or_invited_to_their_trip() {
        $this->withoutMiddleware();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $user3 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->attachInactiveTripUser($trip, $user2);
        $this->maker->attachTripUser($trip, $user3);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/eligibleFriends",
            ['input' => $user2->first_name, 'trip_id' => $trip->id]
        );

        $this->assertEquals(0, sizeof($response->json()));

    }

    /** @test */
    public function a_user_can_invite_a_friend_to_their_trip_by_id() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $user3 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => $user2->id,
                        'display' => $user2->first_name,
                        'type' => 'id'
                    ],
                    [
                        'data' => $user3->id,
                        'display' => $user2->first_name,
                        'type' => 'id'
                    ]
                ]
            ]
        );

        $this->assertDatabaseHas('trip_user', [
            'user_id' => $user2->id,
            'trip_id' => $trip->id,
            'active'  => 0
        ]);

        $this->assertDatabaseHas('trip_user', [
            'user_id' => $user3->id,
            'trip_id' => $trip->id,
            'active'  => 0
        ]);
    }

    /**
    * @group fail
    * @test
    */
    public function a_user_can_invite_a_friend_to_their_trip_by_email() {
        Session::start();
        $user1 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => 'fakeemail@walkichaw.us',
                        'display' => 'somename',
                        'type' => 'email'
                    ],
                    [
                        'data' => 'fakeemail2@walkichaw.us',
                        'display' => 'somename',
                        'type' => 'email'
                    ]
                ]
            ]
        );

        $this->assertDatabaseHas('pending_email_trip', [
            'email' => 'fakeemail@walkichaw.us',
            'trip_id' => $trip->id
        ]);

        $this->assertDatabaseHas('pending_email_trip', [
            'email' => 'fakeemail2@walkichaw.us',
            'trip_id' => $trip->id
        ]);

        Mail::assertSent(AccountInvitation::class);
    }

    /** @test */
    public function it_detects_when_an_email_belongs_to_a_registered_user_and_converts_to_id() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => $user2->email,
                        'display' => 'somename',
                        'type' => 'email'
                    ]
                ]
            ]
        );

        $this->assertDatabaseHas('trip_user', [
            'user_id' => $user2->id,
            'trip_id' => $trip->id,
            'active'  => 0
        ]);

        Mail::assertNotSent(AccountInvitation::class);
    }

    /** @test */
    public function it_fails_validation_on_invalid_email() {
        Session::start();
        $user1 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => 'hey123@',
                        'display' => 'somename',
                        'type' => 'email'
                    ]
                ]
            ]
        );

        $response->assertStatus(422);
    }

    /**@test */
    public function it_fails_validation_on_invalid_id() {
        Session::start();
        $user1 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => 'badvalue7',
                        'display' => 'somename',
                        'type' => 'id'
                    ]
                ]
            ]
        );

        $response->assertStatus(422);
    }

    /**@test  */
    public function it_does_not_invite_a_registered_user_that_was_converted_from_an_email_invite_to_an_id_invite_when_they_are_already_invited() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->attachTripUser($trip, $user2);
        $this->maker->login($user1);

        $response = $this->post(
            "/trip/$trip->id/inviteFriends", [
                '_token' => csrf_token(),
                'friends' => [
                    [
                        'data' => $user2->id,
                        'display' => 'somename',
                        'type' => 'id'
                    ]
                ]
            ]
        );

        $response->assertStatus(200);
        Mail::assertNotSent(AccountInvitation::class);
    }
}
