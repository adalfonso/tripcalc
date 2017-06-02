<?php namespace Tests\Feature;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Post;
use Session;

class TripPostTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;
        $this->trip = $this->maker->trip();
        $this->user = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user);
    }

    /** @test */
    public function it_creates_a_trip_post() {
        Session::start();
        $this->maker->login($this->user);

        $response = $this->post('/trip/' . $this->trip->id . '/posts', [
            '_token' => csrf_token(),
            'content' => 'some post thingssss12123'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'postable_type' => 'App\Trip',
            'content' => 'some post thingssss12123'
        ]);
    }

    /** @test */
    public function it_updates_a_trip_post() {
        Session::start();
        $this->maker->login($this->user);

        $post = $this->maker->post($this->trip, 'App\Trip', $this->user);

        $response = $this->patch('/trip/' . $this->trip->id . '/post/' . $post->id, [
            '_token' => csrf_token(),
            'content' => '092365259289465394728946'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'postable_id' => $this->trip->id,
            'content' => '092365259289465394728946'
        ]);
    }

    /** @test */
    public function it_deletes_a_trip_post() {
        Session::start();
        $this->maker->login($this->user);

        $post = $this->maker->post($this->trip, 'App\Trip', $this->user);

        $response = $this->delete('/trip/' . $this->trip->id . '/post/' . $post->id, [
            '_token' => csrf_token()
        ]);

        $response->assertStatus(200);

        $this->assertEquals(null, Post::find($post->id));
    }
}
