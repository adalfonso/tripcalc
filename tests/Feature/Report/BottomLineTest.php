<?php namespace Tests\Feature\Report;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BottomLine extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;

        $this->trip = $this->maker->trip();
        $this->user1 = $this->maker->user();
        $this->user2 = $this->maker->user();
        $this->user3 = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user1);
        $this->maker->attachTripUser($this->trip, $this->user2);
        $this->maker->attachTripUser($this->trip, $this->user3);
    }

    /** @test */
    public function it_accurately_calculates_total() {
        $transaction1 = $this->maker->transaction(
            $this->trip, $this->user1, 60
        );
        $transaction1->users()->sync([
            $this->user1->id => ['split_ratio' => 21],
            $this->user2->id => ['split_ratio' => 39]
        ]);

        $transaction2 = $this->maker->transaction(
            $this->trip, $this->user1, 60
        );

        $transaction3 = $this->maker->transaction(
            $this->trip, $this->user2, 100
        );

        $transaction4 = $this->maker->transaction(
            $this->trip, $this->user3, 15
        );
        $transaction4->users()->sync([
            $this->user1->id => ['split_ratio' => 10],
            $this->user2->id => ['split_ratio' => 5]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/bottomLine')->json();

        $this->assertEquals(round(35.66), round($response));
    }

    /** @test */
    public function it_can_tell_the_difference_between_credit_and_debit() {
        $transaction = $this->maker->transaction(
            $this->trip, $this->user1, 90
        );
        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/bottomLine')->json();
        $this->assertEquals(60, round($response));

        $this->maker->login($this->user2);
        $response = $this->get('/trips/' . $this->trip->id . '/report/bottomLine')->json();
        $this->assertEquals(-30, round($response));
    }
}
