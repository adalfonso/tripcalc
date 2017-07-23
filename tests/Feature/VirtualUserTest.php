<?php namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Session;
use Tests\DuskTestCase;
use Tests\Library\Maker;

class VitualUserTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        $this->maker = new Maker;
        Session::start();

        $this->user = $this->maker->user();
        $this->user2 = $this->maker->user();
        $this->maker->login($this->user);
        $this->trip = $this->maker->trip();

        $this->virtUser = $this->maker->virtualUser($this->trip);
        $this->maker->attachTripUser($this->trip, $this->user);
        $this->maker->attachTripUser($this->trip, $this->user2);
    }

    /** @test */
    public function it_merges_a_virtual_user_with_no_conflicts() {
        //$this->withoutMiddleware();

        $transaction = $this->maker->transaction($this->trip, $this->user);
        $this->maker->attachTransactionVirtualUser($transaction, $this->virtUser, 5);

        $response = $this->post(
            'trip/' . $this->trip->id .
            '/virtualUser/' . $this->virtUser->id . '/merge', [
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transaction_user', [
            'user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('transaction_virtual_user', [
            'virtual_user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('virtual_users', [
            'id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_detects_merge_conflicts() {
        $transaction = $this->maker->transaction($this->trip, $this->user);
        $this->maker->attachTransactionUser($transaction, $this->user, 1);
        $this->maker->attachTransactionVirtualUser($transaction, $this->virtUser, 5);

        $response = $this->post(
            'trip/' . $this->trip->id .
            '/virtualUser/' . $this->virtUser->id . '/merge', [
                '_token' => csrf_token()
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_merges_a_user_based_conflict() {
        $transaction = $this->maker->transaction($this->trip, $this->user);
        $this->maker->attachTransactionUser($transaction, $this->user, 1);
        $this->maker->attachTransactionVirtualUser($transaction, $this->virtUser, 5);

        $response = $this->post(
            'trip/' . $this->trip->id .
            '/virtualUser/' . $this->virtUser->id . '/merge', [
                '_token' => csrf_token(),
                'rules' => [
                    [
                        'id' => $transaction->id,
                        'conflict' => [
                            'user' => 1, 'virtual' => 5
                        ],
                        'resolution' => 'user'
                    ]
                ]
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transaction_user', [
            'user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 1
        ]);

        $this->assertDatabaseMissing('transaction_virtual_user', [
            'virtual_user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('virtual_users', [
            'id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_merges_a_virtual_based_conflict() {
        $transaction = $this->maker->transaction($this->trip, $this->user);
        $this->maker->attachTransactionUser($transaction, $this->user, 1);
        $this->maker->attachTransactionVirtualUser($transaction, $this->virtUser, 5);

        $response = $this->post(
            'trip/' . $this->trip->id .
            '/virtualUser/' . $this->virtUser->id . '/merge', [
                '_token' => csrf_token(),
                'rules' => [
                    [
                        'id' => $transaction->id,
                        'conflict' => [
                            'user' => 1, 'virtual' => 5
                        ],
                        'resolution' => 'virtual'
                    ]
                ]
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transaction_user', [
            'user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('transaction_virtual_user', [
            'virtual_user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('virtual_users', [
            'id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_merges_a_combined_based_conflict() {
        $transaction = $this->maker->transaction($this->trip, $this->user);
        $this->maker->attachTransactionUser($transaction, $this->user, 1);
        $this->maker->attachTransactionVirtualUser($transaction, $this->virtUser, 5);

        $response = $this->post(
            'trip/' . $this->trip->id .
            '/virtualUser/' . $this->virtUser->id . '/merge', [
                '_token' => csrf_token(),
                'rules' => [
                    [
                        'id' => $transaction->id,
                        'conflict' => [
                            'user' => 1, 'virtual' => 5
                        ],
                        'resolution' => 'combine'
                    ]
                ]
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transaction_user', [
            'user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 6
        ]);

        $this->assertDatabaseMissing('transaction_virtual_user', [
            'virtual_user_id' => $this->user->id,
            'transaction_id' => $transaction->id,
            'split_ratio' => 5
        ]);

        $this->assertDatabaseMissing('virtual_users', [
            'id' => $this->user->id
        ]);
    }
}
