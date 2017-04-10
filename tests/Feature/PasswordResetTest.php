<?php namespace Tests\Feature;

use App\Mail\PasswordReset;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;

class PasswordResetTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        Mail::fake();
        $this->maker = new Maker;
    }

    /** @test */
    public function it_creates_a_password_reset() {
        Session::start();
        $user = $this->maker->user();

        $response = $this->post('password/email', [
            '_token' => csrf_token(),
            'email' => $user->email
        ]);

        $response->assertStatus(302);

        Mail::assertSent(PasswordReset::class);
    }

    /** @test */
    public function it_lets_a_user_reset_their_password() {
        Session::start();
        $user = $this->maker->user();

        $response = $this->post('password/email', [
            '_token' => csrf_token(),
            'email' => $user->email
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('password_resets', [
            'email' => $user->email
        ]);

        Mail::assertSent(PasswordReset::class, function($mail) {
            $this->token = $mail->token();
            return true;
        });

        $response = $this->post('password/reset', [
            '_token' => csrf_token(),
            'token' => $this->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(302);

        $entry = \DB::select('
            SELECT *
            FROM password_resets
            WHERE email = ?
        ', [$user->email]);

        $this->assertEquals(0, sizeof($entry));

    }

    /** @test */
    public function it_doesnt_reset_using_expired_reset_tokens() {
        Session::start();
        $user = $this->maker->user();

        $response = $this->post('password/email', [
            '_token' => csrf_token(),
            'email' => $user->email
        ]);

        $response->assertStatus(302);

        DB::select('
            UPDATE password_resets
            SET created_at = ?
            WHERE email = ?
        ', [\Carbon\Carbon::yesterday(), $user->email]);

        Mail::assertSent(PasswordReset::class, function($mail) {
            $this->token = $mail->token();
            return true;
        });

        $response = $this->post('password/reset', [
            '_token' => csrf_token(),
            'token' => $this->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}