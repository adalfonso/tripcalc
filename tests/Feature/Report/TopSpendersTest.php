<?php namespace Tests\Feature\Report;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TopSpendersTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;

        $this->trip = $this->maker->trip();
        $this->user1 = $this->maker->user();
        $this->user2 = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user1);
        $this->maker->attachTripUser($this->trip, $this->user2);

        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user1
        );
    }

    /** @test */
    public function it_limits_the_number_of_users_to_5() {
        $user3 = $this->maker->user();
        $this->maker->transaction($this->trip, $user3, 150);
        $user4 = $this->maker->user();
        $this->maker->transaction($this->trip, $user4, 100);
        $user5 = $this->maker->user();
        $this->maker->transaction($this->trip, $user5, 75);
        $user6 = $this->maker->user();
        $this->maker->transaction($this->trip, $user6, 50);

        $this->transaction->amount = 200;
        $this->transaction->save();

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/topSpenders')->json();

        $this->assertEquals(2, sizeof($response));
        $this->assertEquals(5, sizeof($response['spend']));
    }

    /** @test */
    public function it_calculates_totals_accurately() {
        $this->transaction->amount = 100;
        $this->transaction->save();

        $transaction2 = $this->maker->transaction($this->trip, $this->user2, 50);

        $user3 = $this->maker->user();
        $transaction3 = $this->maker->transaction($this->trip, $user3, 25);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/topSpenders')->json();

        $this->assertEquals(3, sizeof($response['spend']));

        $this->assertEquals(100, $response['spend'][0]['sum']);
        $this->assertEquals('100.00', $response['spend'][0]['currency']);
        $this->assertEquals(57, $response['spend'][0]['percent']);

        $this->assertEquals(50, $response['spend'][1]['sum']);
        $this->assertEquals(29, $response['spend'][1]['percent']);

        $this->assertEquals(25, $response['spend'][2]['sum']);
        $this->assertEquals(14, $response['spend'][2]['percent']);
    }
}
