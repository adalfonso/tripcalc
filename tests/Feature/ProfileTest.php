<?php namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;

class ProfileTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        $this->maker = new Maker;
    }

    /** @test */
    public function a_user_can_update_their_basic_info() {
        $this->withoutMiddleware();
        $user = $this->maker->user();
        $this->maker->login($user);

        $response = $this->patch(
            "/user",
            ['first_name' => 'somefakename', 'last_name' => 'anotherfakename']
        );

        $updatedUser = \App\User::find($user->id);

        $this->assertEquals('somefakename', $updatedUser->first_name);
        $this->assertEquals('anotherfakename', $updatedUser->last_name);
    }
}
