<?php namespace Tests\Feature\Report;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Detailed extends DuskTestCase {

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
    public function it_gets_a_user_paid_transaction_with_even_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user1, 60
        );

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(40, $response['net']);
    }

    /** @test */
    public function it_gets_a_user_paid_transaction_with_uneven_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user1, 60
        );

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 21],
            $this->user2->id => ['split_ratio' => 39]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(39, $response['net']);

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 45],
            $this->user2->id => ['split_ratio' => 10],
            $this->user3->id => ['split_ratio' => 5]
        ]);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(15, $response['net']);
    }

    /** @test */
    public function it_gets_a_user_paid_transaction_with_personal_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user1, 60
        );

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 21]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(0, $response['net']);
    }

    /** @test */
    public function it_gets_a_non_user_paid_transaction_with_even_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user2, 60
        );

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-20, $response['net']);
    }

    /** @test */
    public function it_gets_a_non_user_paid_transaction_with_uneven_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user2, 60
        );

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 21],
            $this->user2->id => ['split_ratio' => 39]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-21, $response['net']);

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 45],
            $this->user2->id => ['split_ratio' => 10],
            $this->user3->id => ['split_ratio' => 5]
        ]);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-45, $response['net']);
    }

    /** @test */
    public function it_gets_a_non_user_paid_transaction_with_personal_split() {
        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user2, 60
        );

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 21]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-60, $response['net']);
    }

    /** @test */
    public function it_gets_a_user_paid_transaction_with_even_split_when_there_is_only_one_user() {
        $trip = $this->maker->trip();
        $user = $this->maker->user();
        $this->maker->attachTripUser($trip, $user);

        $this->maker->transaction($trip, $user, 60);

        $this->maker->login($user);

        $response = $this->get('/trips/' . $trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(0, $response['net']);
    }

    /** @test */
    public function it_gets_a_user_paid_transaction_with_personal_split_when_there_is_only_one_user() {
        $trip = $this->maker->trip();
        $user = $this->maker->user();
        $this->maker->attachTripUser($trip, $user);

        $transaction = $this->maker->transaction($trip, $user, 60);

        $transaction->users()->sync([
            $user->id => ['split_ratio' => 21]
        ]);

        $this->maker->login($user);

        $response = $this->get('/trips/' . $trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(0, $response['net']);
    }

    /** @test */
    public function it_gets_a_non_user_paid_transaction_with_even_split_when_there_is_only_one_user() {
        $trip = $this->maker->trip();
        $user = $this->maker->user();
        $this->maker->attachTripUser($trip, $user);

        // Not sure how this would be possible, but, YOLO
        $this->maker->transaction($trip, $this->user2, 60);

        $this->maker->login($user);

        $response = $this->get('/trips/' . $trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-60, $response['net']);
    }

    /** @test */
    public function it_gets_a_non_user_paid_transaction_with_personal_split_when_there_is_only_one_user() {
        $trip = $this->maker->trip();
        $user = $this->maker->user();
        $this->maker->attachTripUser($trip, $user);

        $transaction = $this->maker->transaction($trip, $this->user2, 60);

        $transaction->users()->sync([
            $user->id => ['split_ratio' => 21]
        ]);

        $this->maker->login($user);

        $response = $this->get('/trips/' . $trip->id . '/report/detailed')->json()['transactions'][0];

        $this->assertEquals(60, $response['amount']);
        $this->assertEquals(-60, $response['net']);
    }
}
