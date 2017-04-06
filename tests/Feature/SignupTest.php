<?php namespace Tests\Feature;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Session;

class SignupTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;
    }

    /** @test */
    public function it_fails_no_information_is_sent() {
        Session::start();

        $response = $this->post('/register', ['_token' => csrf_token()]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'username', 'email', 'password'
        ]);
    }

    /** @test */
    public function it_fails_when_no_information_is_sent() {
        Session::start();

        $response = $this->post('/register', ['_token' => csrf_token()]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'username', 'email', 'password'
        ]);
    }

    /** @test */
    public function it_fails_when_too_many_characters_entered() {
        Session::start();

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'first_name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'last_name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'username' => 'aaaaaaaaaaaaaaaa',
            'email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'username', 'email'
        ]);
    }

    /** @test */
    public function it_fails_when_too_few_characters_entered() {
        Session::start();

        $response = $this->post('/register', [
            '_token' => csrf_token(), 'password' => 'a'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_fails_when_confirmations_not_entered() {
        Session::start();

          $response = $this->post('/register', [
            '_token' => csrf_token(),
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'username' => 'username',
            'email' => 'person@gmail.com',
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password', 'email']);
    }

    /** @test */
    public function it_fails_when_confirmations_dont_match() {
        Session::start();

          $response = $this->post('/register', [
            '_token' => csrf_token(),
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'username' => 'username',
            'email' => 'person@gmail.com',
            'email_confirmation' => 'person@gmail.co',
            'password' => 'password',
            'password_confirmation' => 'passwor'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password', 'email']);
    }

    /** @test */
    public function it_fails_when_username_or_email_already_taken() {

        $user = $this->maker->user();

        Session::start();

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'username' => $user->username,
            'email' => $user->email,
            'email_confirmation' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username', 'email']);
    }

    /** @test */
    public function it_succeeds() {

        Session::start();

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'username' => 'username',
            'email' => 'iosdufijefiesnfiuse@walkichaw.us',
            'email_confirmation' => 'iosdufijefiesnfiuse@walkichaw.us',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_migrates_pending_trips() {

        \App\PendingEmailTrip::create([
            'email'   => 'iosdufijefiesnfiuse@walkichaw.us',
            'trip_id' => 1
        ]);

        Session::start();

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'username' => 'username',
            'email' => 'iosdufijefiesnfiuse@walkichaw.us',
            'email_confirmation' => 'iosdufijefiesnfiuse@walkichaw.us',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $user = \App\User::where('email', 'iosdufijefiesnfiuse@walkichaw.us')->first();

        $this->assertEquals(1, $user->trips->count());
        $this->assertEquals(0, $user->trips->first()->pivot->active);
    }
}