<?php namespace Tests\Feature;

use App\Mail\AccountInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;

class InviteTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        Mail::fake();
        $this->maker = new Maker;
    }

    /** @test */
    public function a_user_can_accept_a_trip_invite() {
        Session::start();
        $user1 = $this->maker->user();
        $user2 = $this->maker->user();
        $trip = $this->maker->trip();
        $this->maker->attachTripUser($trip, $user1);
        $this->maker->login($user2);

        $trip->users()->attach($user2->id);

        $this->assertEquals(1, $user2->pendingTripRequests->count());

        $response = $this->post('/trip/' . $trip->id . '/resolveRequest', [
            '_token' => csrf_token(),
            'resolution' => 1
        ]);

        $this->assertEquals(0, $user2->fresh()->pendingTripRequests->count());
    }
}
