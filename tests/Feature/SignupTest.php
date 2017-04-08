<?php namespace Tests\Feature;

use App\Mail\AccountActivation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;
use App\User;

class SignupTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        Mail::fake();
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
            'email' => 'sfsdf@walkichaw.us',
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
            'email' => 'sfsdf@walkichaw.us',
            'email_confirmation' => 'sfsdf@walkichaw.ug',
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
            'email' => 'test123@walkichaw.us',
            'email_confirmation' => 'test123@walkichaw.us',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $user = User::where('email', 'test123@walkichaw.us')->first();

        $this->assertDatabaseHas('user_activations', [
            'user_id' => $user->id
        ]);

        $response->assertRedirect('/login');

        Mail::assertSent(AccountActivation::class, function($mail) use($user) {
            return $mail->hasTo($user->email) &&
                $mail->link() === route('user.activate', $user->activation->token);
        });
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

    /** @test */
    public function it_resends_an_activation_email_if_two_hours_has_passed() {

        Session::start();
        $user = $this->maker->user();
        $user->activated = false;
        $user->save();

        $yesterday = \Carbon\Carbon::yesterday();

        $activation = new \App\UserActivation;
        $activation->user_id = $user->id;
        $activation->token = '1234';
        $activation->created_at = $yesterday;
        $activation->updated_at = $yesterday;
        $activation->save();

        $this->maker->login($user);

        $response = $this->get('/user/activation/resend');

        Mail::assertSent(AccountActivation::class);
    }

    /** @test */
    public function it_doesnt_resend_an_activation_email_in_less_than_two_hours() {

        Session::start();
        $user = $this->maker->user();
        $user->activated = false;
        $user->save();

        $now = \Carbon\Carbon::now();

        $activation = new \App\UserActivation;
        $activation->user_id = $user->id;
        $activation->token = '1234';
        $activation->created_at = $now;
        $activation->updated_at = $now;
        $activation->save();

        $this->maker->login($user);

        $response = $this->get('/user/activation/resend');

        Mail::assertNotSent(AccountActivation::class);
    }
}