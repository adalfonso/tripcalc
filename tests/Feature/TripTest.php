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
    public function it_gets_activities_when_there_are_posts_but_no_transactions() {
        $this->maker->login($this->user);

        $this->post = $this->maker->post(
            $this->trip, $this->user
        );

        $response = $this->get('/trips/' . $this->trip->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_activities_when_there_are_transactions_but_no_posts() {
        $this->maker->login($this->user);

        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user
        );

        $response = $this->get('/trips/' . $this->trip->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_doesnt_blow_up_when_there_are_no_activities() {
        $this->maker->login($this->user);

        $response = $this->get('/trips/' . $this->trip->id);

        $response->assertStatus(200);
    }
}
