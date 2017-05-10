<?php namespace Tests\Feature;

use Tests\DuskTestCase;
use Tests\Library\Maker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Transaction;
use Session;

class TransactionTest extends DuskTestCase {

    use DatabaseTransactions;

    public function setUp() {
        parent::setUp();
        $this->maker = new Maker;

        $this->trip = $this->maker->trip();
        $this->user1 = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $this->user1);

        $this->transaction = $this->maker->transaction(
            $this->trip, $this->user1
        );
    }

    /** @test */
    public function it_fetches_a_transaction() {
        $this->maker->login($this->user1);
        $response = $this
            ->get('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id)
            ->json();

        $this->assertEquals($this->trip->id, $response['transaction']['trip_id']);
        $this->assertEquals($this->transaction->id, $response['transaction']['id']);
        $this->assertEquals(
            $this->transaction->hashtags->first()->tag,
            $response['hashtags'][0]
        );
        $this->assertEquals(1, sizeof($response['travelers']));
    }

    /** @test */
    public function it_doesnt_fetch_a_transaction_for_the_wrong_trip() {
        $this->trip2 = $this->maker->trip();
        $this->maker->attachTripUser($this->trip2, $this->user1);

        $this->maker->login($this->user1);

        $response = $this->get(
            '/trips/' . $this->trip2->id . '/transactions/' . $this->transaction->id
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function it_doesnt_fetch_a_transaction_for_users_not_on_trip() {
        $this->user2 = $this->maker->user();
        $this->maker->login($this->user2);

        $response = $this->get(
            '/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id
        );

        $response->assertRedirect('/trips/dashboard');
    }

    /** @test */
    public function it_creates_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->post('/trips/' . $this->trip->id . '/transactions/', [
            '_token' => csrf_token(),
            'amount'   => 5,
            'date'     => '2010-1-1',
            'description' => '',
            'hashtags' => [
                'items' => [1, 2, 3]
            ],
            'split' => [
                'travelers' => [
                    [
                        'id' => $this->user1->id,
                        'split_ratio' => 2,
                        'is_spender' => true
                    ]
                ]
            ]
        ]);

        $transaction = Transaction::find(
            $response->json()['id']
        );

        $this->assertNotNull($transaction);
    }

    /** @test */
    public function it_updates_transaction_data() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ]
        ]);

        $find = Transaction::find($this->transaction->id);

        $this->assertEquals(666, $find->amount);
        $this->assertEquals('2010-12-12', $find->date);
        $this->assertEquals('hello', $find->description);
    }

    /** @test */
    public function it_adds_spenders_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [
                'travelers' => [
                    [
                        'id' => $this->user1->id,
                        'split_ratio' => 2.00,
                        'is_spender' => true
                    ]
                ]
            ]
        ]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(1, $spenders->count());
        $this->assertEquals(2, $spenders->first()->pivot->split_ratio);
    }

    /** @test */
    public function it_updates_spenders_on_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $this->transaction->users()->attach($this->user1, ['split_ratio' => 5]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(1, $spenders->count());

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [
                'travelers' => [
                    [
                        'id' => $this->user1->id,
                        'split_ratio' => 2.00,
                        'is_spender' => true
                    ]
                ]
            ]
        ]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(1, $spenders->count());
        $this->assertEquals(2, $spenders->first()->pivot->split_ratio);
    }

    /** @test */
    public function it_removes_spenders_from_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $this->transaction->users()->attach($this->user1, ['split_ratio' => 5]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(1, $spenders->count());

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ]
        ]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(0, $spenders->count());
    }

    /** @test */
    public function it_adds_hashtags_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => ['hello'] ],
            'split' => [ 'travelers' => [] ]
        ]);

        $hashtags = $this->transaction->fresh()->hashtags;

        $this->assertEquals(1, $hashtags->count());
        $this->assertEquals(2, $hashtags->first()->tag === 'hello');
    }

    /** @test */
    public function it_removes_hashtags_from_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $hashtags = $this->transaction->fresh()->hashtags;
        $this->assertEquals(2, $hashtags->count());

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ]
        ]);

        $hashtags = $this->transaction->fresh()->hashtags;

        $this->assertEquals(0, $hashtags->count());
    }

    /** @test */
    public function it_doesnt_add_invalid_hashtags_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $this->transaction->hashtags()->detach();

        $transaction = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [' dsf', 'dfgdf,g', '#sdf'] ],
            'split' => [ 'travelers' => [] ]
        ]);
        $hashtags = $this->transaction->fresh()->hashtags;
        $this->assertEquals(0, $hashtags->count());
    }

    /** @test */
    public function it_deletes_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id . '/delete' , [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ],
            'delete' => true,
            'delete_confirmation' => true,
            'password' =>'password'
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_fails_to_delete_a_transaction_with_invalid_password() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id . '/delete' , [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ],
            'delete' => true,
            'delete_confirmation' => true,
            'password' =>'wrongpassword'
        ]);

        $response->assertStatus(422)->assertJson([
            'password' => ['The password you entered is incorrect.']
        ]);
    }

    /** @test */
    public function it_fails_to_delete_a_transaction_for_non_privileged_user() {
        Session::start();
        $this->user2 = $this->maker->user();
        $this->maker->login($this->user2);

        $response = $this->post('/trips/' . $this->trip->id . '/transactions/' . $this->transaction->id . '/delete' , [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [ 'items' => [] ],
            'split' => [ 'travelers' => [] ],
            'delete' => true,
            'delete_confirmation' => true,
            'password' =>'password'
        ]);

        $response->assertStatus(302);
    }
}
