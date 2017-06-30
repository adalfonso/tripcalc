<?php namespace Tests\Feature\Report;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CloseoutTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;

        $this->trip = $this->maker->trip();
        $this->user1 = $this->maker->user();
        $this->user2 = $this->maker->user();
        $this->user3 = $this->maker->user();
        $this->user4 = $this->maker->user();
        $this->user5 = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user1);
        $this->maker->attachTripUser($this->trip, $this->user2);
        $this->maker->attachTripUser($this->trip, $this->user3);
        $this->maker->attachTripUser($this->trip, $this->user4);
        $this->maker->attachTripUser($this->trip, $this->user5);
    }

    /** @test */
    public function it_accurately_calculate_credit_and_debits() {
        $this->maker->login($this->user1);

        $transaction1 = $this->maker->transaction(
            $this->trip, $this->user1, 200
        );
        $transaction1->users()->sync([
            $this->user1->id => ['split_ratio' => 20],
        ]);

        $transaction2 = $this->maker->transaction(
            $this->trip, $this->user1, 100
        );

        $transaction3 = $this->maker->transaction(
            $this->trip, $this->user2, 132
        );
        $transaction3->users()->sync([
            $this->user1->id => ['split_ratio' => 20],
            $this->user2->id => ['split_ratio' => 20],
            $this->user3->id => ['split_ratio' => 20],
            $this->user4->id => ['split_ratio' => 20]
        ]);

        $transaction4 = $this->maker->transaction(
            $this->trip, $this->user4, 111
        );
        $transaction4->users()->sync([
            $this->user1->id => ['split_ratio' => 100],
            $this->user2->id => ['split_ratio' => 11],
        ]);

        $transaction5 = $this->maker->transaction(
            $this->trip, $this->user3, 167
        );
        $transaction5->users()->sync([
            $this->user1->id => ['split_ratio' => 30],
            $this->user2->id => ['split_ratio' => 43],
            $this->user3->id => ['split_ratio' => 67],
            $this->user4->id => ['split_ratio' => 18],
            $this->user5->id => ['split_ratio' => 9]
        ]);

        $spenders = $this->get('/trip/' . $this->trip->id . '/report/closeout')->json()['spenders'];


        foreach ($spenders as $spender) {
            $error = sizeof($spender['credits']) > 0 && sizeof($spender['debits']) > 0;
            $this->assertEquals(false, $error);
            $this->assertEquals(0, $spender['total']);
        }

        $this->assertEquals(47, $spenders[0]['debits'][$this->user3->type . '_' . $this->user3->id]);
        $this->assertEquals(36, $spenders[0]['debits'][$this->user4->type . '_' . $this->user4->id]);
        $this->assertEquals(4, $spenders[1]['debits'][$this->user4->type . '_' . $this->user4->id]);
        $this->assertEquals(25, $spenders[1]['debits'][$this->user2->type . '_' . $this->user2->id]);
        $this->assertEquals(25, $spenders[2]['credits'][$this->user5->type . '_' . $this->user5->id]);
        $this->assertEquals(36, $spenders[3]['credits'][$this->user1->type . '_' . $this->user1->id]);
        $this->assertEquals(4, $spenders[3]['credits'][$this->user5->type . '_' . $this->user5->id]);
        $this->assertEquals(47, $spenders[4]['credits'][$this->user1->type . '_' . $this->user1->id]);
    }

    /** @test */
    public function it_doesnt_have_rounding_errors() {
        $this->maker->login($this->user1);

        $transaction = $this->maker->transaction(
            $this->trip, $this->user2, 127.3
        );
        $transaction->users()->sync([
            $this->user1->id => ['split_ratio' => 14],
            $this->user3->id => ['split_ratio' => 5],
            $this->user4->id => ['split_ratio' => 25],
            $this->user5->id => ['split_ratio' => 25]
        ]);

            $spenders = $this->get('/trip/' . $this->trip->id . '/report/closeout')->json()['spenders'];

        foreach ($spenders as $spender) {
            $error = sizeof($spender['credits']) > 0 && sizeof($spender['debits']) > 0;
            $this->assertEquals(false, $error);
            $this->assertEquals(0, $spender['total']);
        }
    }

    /** @test */
    public function all_totals_are_zero() {
        $this->maker->login($this->user1);

        $transaction1 = $this->maker->transaction(
            $this->trip, $this->user4, 57.79
        );
        $transaction1->users()->sync([
            $this->user1->id => ['split_ratio' => 1],
            $this->user2->id => ['split_ratio' => 3.14],
            $this->user3->id => ['split_ratio' => 666],
            $this->user4->id => ['split_ratio' => 4]
        ]);

        $transaction2 = $this->maker->transaction(
            $this->trip, $this->user2, 127.3
        );
        $transaction2->users()->sync([
            $this->user1->id => ['split_ratio' => 14],
            $this->user2->id => ['split_ratio' => 3.1],
            $this->user3->id => ['split_ratio' => 5],
            $this->user4->id => ['split_ratio' => 25]
        ]);

        $spenders = $this->get('/trip/' . $this->trip->id . '/report/closeout')->json()['spenders'];

        foreach ($spenders as $spender) {
            $error = sizeof($spender['credits']) > 0 && sizeof($spender['debits']) > 0;
            $this->assertEquals(false, $error);
            $this->assertEquals(0, $spender['total']);
        }
    }
}
