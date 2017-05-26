<?php namespace Tests\Feature;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Trip;
use Session;

class TripTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;

        $this->trip = $this->maker->trip();
        $this->user = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user);
    }

    /** @test */
    public function it_creates_a_trip() {
        Session::start();
        $this->maker->login($this->user);

        $response = $this->post('/trips', [
            '_token' => csrf_token(),
            'name' => 'somefaketrip123456',
            'start_date' => '2017-1-1',
            'end_date' => '2017-1-3',
            'budget' => 50,
            'description' => 'sample description',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trips', [
            'name' => 'somefaketrip123456'
        ]);
    }

    /** @test */
    public function it_updates_a_trip() {
        Session::start();
        $this->maker->login($this->user);

        $response = $this->patch('/trip/' . $this->trip->id, [
            '_token' => csrf_token(),
            'name' => 'somefaketrip123456',
            'start_date' => '2017-1-1',
            'end_date' => '2017-1-3',
            'budget' => 50,
            'description' => 'sample description',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trips', [
            'id' => $this->trip->id,
            'name' => 'somefaketrip123456'
        ]);
    }

    /** @test */
    public function it_deletes_a_trip() {
        Session::start();
        $this->maker->login($this->user);

        $response = $this->delete('/trip/' . $this->trip->id, [
            '_token' => csrf_token(),
            'password' => 'password'
        ]);

        $response->assertStatus(200);

        $this->assertEquals(null, Trip::find($this->trip->id));
    }

    /** @test */
    public function it_gets_activities_when_there_are_posts_but_no_transactions() {
        $this->maker->login($this->user);

        $this->post = $this->maker->post(
            $this->trip, 'trip', $this->user
        );

        $response = $this->get('/trip/' . $this->trip->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_activities_when_there_are_transactions_but_no_posts() {
        $this->maker->login($this->user);

        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user
        );

        $response = $this->get('/trip/' . $this->trip->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_doesnt_blow_up_when_there_are_no_activities() {
        $this->maker->login($this->user);

        $response = $this->get('/trip/' . $this->trip->id);

        $response->assertStatus(200);
    }
}
