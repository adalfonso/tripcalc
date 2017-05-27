<?php namespace Tests\Feature;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Post;
use Session;

class ProfilePostTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;
        $this->trip = $this->maker->trip();
        $this->user1 = $this->maker->user();
        $this->user2 = $this->maker->user();
        $this->maker->activeFriendship($this->user1, $this->user2);
    }

    /** @test */
    public function it_creates_a_profile_post() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->post('/profile/' . $this->user2->id . '/posts', [
            '_token' => csrf_token(),
            'content' => 'hey best but, here i a a post'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'postable_type' => 'App\User',
            'content' => 'hey best but, here i a a post'
        ]);

        $post = \App\Post::where('content', 'hey best but, here i a a post')->get();

        $this->assertEquals(1, $post->count());
    }

    /** @test */
    public function it_doesnt_create_a_profile_post_for_non_friends() {
        Session::start();
        $this->maker->login($this->user1);

        $user3 = $this->maker->user();

        $response = $this->post('/profile/' . $user3->id . '/posts', [
            '_token' => csrf_token(),
            'content' => 'hey best but, here i a a post'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_updates_a_profile_post() {
        Session::start();
        $this->maker->login($this->user1);

        $post = $this->maker->post($this->user2, 'user', $this->user1);

        $response = $this->patch('/profile/' . $this->user2->id . '/post/' . $post->id, [
            '_token' => csrf_token(),
            'content' => '092365259289465394728946'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'postable_id' => $this->user2->id,
            'postable_type' => 'App\User',
            'content' => '092365259289465394728946'
        ]);
    }

    /** @test */
    public function it_deletes_a_profile_post() {
        Session::start();
        $this->maker->login($this->user1);

        $post = $this->maker->post($this->user2, 'user', $this->user1);

        $response = $this->delete('/profile/' . $this->user2->id . '/post/' . $post->id, [
            '_token' => csrf_token()
        ]);

        $response->assertStatus(200);

        $this->assertEquals(null, Post::find($post->id));
    }
}
