<?php namespace Tests\Feature\Report;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProgressTest extends DuskTestCase {

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
    public function it_accounts_for_all_users_on_a_trip() {
        $this->maker->login($this->user1);
        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(2, sizeof($response));
    }

    /** @test */
    public function it_accounts_for_all_users_who_entered_a_transaction() {
        $user = $this->maker->user();
        $this->maker->transaction($this->trip, $user);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(3, sizeof($response));
    }

    /** @test */
    public function it_accounts_for_all_users_who_are_responsible_for_split() {
        $user = $this->maker->user();
        $this->transaction->users()->sync([
            $user->id => ['split_ratio' => 5]
        ]);

        $this->maker->login($this->user1);

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(3, sizeof($response));
    }

    /** @test */
    public function it_handles_even_split() {
        $this->maker->login($this->user1);

        $this->transaction->amount = 100;
        $this->transaction->save();

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(-50, collect($response)->where('id', $this->user1->id)->first()['total']);
        $this->assertEquals(50, collect($response)->where('id', $this->user2->id)->first()['total']);

        $transaction = $this->maker->transaction(
            $this->trip, $this->user2
        );
        $transaction->amount = 500;
        $transaction->save();

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(200, collect($response)->where('id', $this->user1->id)->first()['total']);
        $this->assertEquals(-200, collect($response)->where('id', $this->user2->id)->first()['total']);
    }

    /** @test */
    public function it_handles_uneven_split() {
        $this->maker->login($this->user1);

        $this->transaction->amount = 100;
        $this->transaction->save();

        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 10],
            $this->user2->id => ['split_ratio' => 90]
        ]);

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(-90, collect($response)->where('id', $this->user1->id)->first()['total']);
        $this->assertEquals(90, collect($response)->where('id', $this->user2->id)->first()['total']);

        $transaction = $this->maker->transaction(
            $this->trip, $this->user2, 200
        );
        $transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 20],
            $this->user2->id => ['split_ratio' => 80]
        ]);

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(-50, collect($response)->where('id', $this->user1->id)->first()['total']);
        $this->assertEquals(50, collect($response)->where('id', $this->user2->id)->first()['total']);
    }

    /** @test */
    public function report_sums_to_zero() {
        $this->maker->login($this->user1);

        $this->transaction->amount = 100;
        $this->transaction->save();
        $this->transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 10],
            $this->user2->id => ['split_ratio' => 90]
        ]);
        $transaction = $this->maker->transaction(
            $this->trip, $this->user2, 200
        );
        $transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 20],
            $this->user2->id => ['split_ratio' => 80]
        ]);

        $response = $this->get('/trips/' . $this->trip->id . '/report/distribution')->json();

        $this->assertEquals(0, round(collect($response)->sum('total')));
    }
}
