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
            ->get('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id)
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
            '/trip/' . $this->trip2->id . '/transaction/' . $this->transaction->id
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function it_doesnt_fetch_a_transaction_for_users_not_on_trip() {
        $this->user2 = $this->maker->user();
        $this->maker->login($this->user2);

        $response = $this->get(
            '/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id
        );

        $response->assertRedirect('/trips');
    }

    /** @test */
    public function it_doesnt_fetch_user_accounts_that_are_inactive() {
        $user2 = $this->maker->user();
        $user2->activated = false;
        $this->maker->attachTripUser($this->trip, $user2);
        $user2->save();

        $this->maker->login($this->user1);

        $response = $this->get(
            '/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id
        )->json();

        $this->assertEquals(1, sizeof($response['travelers']));
    }

    /** @test */
    public function it_doesnt_fetch_users_who_are_inactive_on_the_trip() {
        $user2 = $this->maker->user();
        $this->maker->attachTripUser($this->trip, $user2);

        $user2->trips()->updateExistingPivot($this->trip->id, ['active' => 0]);

        $this->maker->login($this->user1);

        $response = $this->get(
            '/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id
        )->json();

        $this->assertEquals(1, sizeof($response['travelers']));
    }


    /** @test */
    public function it_creates_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->post('/trip/' . $this->trip->id . '/transactions', [
            '_token' => csrf_token(),
            'amount'   => 5,
            'date'     => '2010-1-1',
            'description' => '',
            'hashtags' => [1, 2, 3],
            'split' => [
                [
                    'id' => $this->user1->id,
                    'type' => 'regular',
                    'split_ratio' => 2,
                    'is_spender' => true
                ]
            ]
        ]);

        $transaction = Transaction::find(
            $response->json()['id']
        );

        $this->assertNotNull($transaction);
    }

    /** @test */
    public function it_doesnt_create_a_transaction_when_the_trip_is_closed() {
        Session::start();
        $this->maker->login($this->user1);

        $this->trip->active = false;
        $this->trip->save();

        $response = $this->post('/trip/' . $this->trip->id . '/transactions', [
            '_token' => csrf_token(),
            'amount'   => 5,
            'date'     => '2010-1-1',
            'description' => '2342346356345234234346536',
            'hashtags' => [1, 2, 3],
            'split' => [
                [
                    'id' => $this->user1->id,
                    'type' => 'regular',
                    'split_ratio' => 2,
                    'is_spender' => true
                ]
            ]
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('transactions', [
            'description' => '2342346356345234234346536'
        ]);
    }

    /** @test */
    public function it_updates_transaction_data() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => []
        ]);

        $find = Transaction::find($this->transaction->id);

        $this->assertEquals(666, $find->amount);
        $this->assertEquals('2010-12-12', $find->date);
        $this->assertEquals('hello', $find->description);
    }

    /** @test */
    public function it_doesnt_update_transaction_data_when_trip_is_closed() {
        Session::start();
        $this->maker->login($this->user1);
        $this->trip->active = false;
        $this->trip->save();

        $response = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => '2342346356345234234346536',
            'hashtags' => [],
            'split' => []
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('transactions', [
            'description' => '2342346356345234234346536'
        ]);
    }

    /** @test */
    public function it_adds_spenders_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [
                [
                    'id' => $this->user1->id,
                    'type' => 'regular',
                    'split_ratio' => 2.00,
                    'is_spender' => true
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

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [
                [
                    'id' => $this->user1->id,
                    'type' => 'regular',
                    'split_ratio' => 2.00,
                    'is_spender' => true
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

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => []
        ]);

        $spenders = $this->transaction->fresh()->users;
        $this->assertEquals(0, $spenders->count());
    }

    /** @test */
    public function it_adds_hashtags_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => ['hello'],
            'split' => []
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

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => []
        ]);

        $hashtags = $this->transaction->fresh()->hashtags;

        $this->assertEquals(0, $hashtags->count());
    }

    /** @test */
    public function it_doesnt_add_invalid_hashtags_to_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $this->transaction->hashtags()->detach();

        $transaction = $this->patch('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [' dsf', 'dfgdf,g', '#sdf'],
            'split' => []
        ]);
        $hashtags = $this->transaction->fresh()->hashtags;
        $this->assertEquals(0, $hashtags->count());
    }

    /** @test */
    public function it_deletes_a_transaction() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->delete('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [],
            'deletable' => true,
            'password' =>'password'
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_doesnt_delete_a_transaction_when_trip_is_closed() {
        Session::start();
        $this->maker->login($this->user1);
        $this->trip->active = false;
        $this->trip->save();

        $response = $this->delete('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [],
            'deletable' => true,
            'password' =>'password'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('transactions', [
            'description' => '2342346356345234234346536'
        ]);
    }

    /** @test */
    public function it_fails_to_delete_a_transaction_with_invalid_password() {
        Session::start();
        $this->maker->login($this->user1);

        $response = $this->delete('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [],
            'deletable' => true,
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

        $response = $this->delete('/trip/' . $this->trip->id . '/transaction/' . $this->transaction->id, [
            '_token' => csrf_token(),
            'amount'   => 666,
            'date'     => '2010-12-12',
            'description' => 'hello',
            'hashtags' => [],
            'split' => [],
            'delete' => true,
            'delete_confirmation' => true,
            'password' =>'password'
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_accurately_gets_split_type() {
        Session::start();
        $this->user2 = $this->maker->user();
        $this->maker->login($this->user1);

        $this->transaction->users()->detach();
        $this->assertEquals('even', $this->transaction->splitType);

        $this->transaction->users()->attach($this->user1, ['split_ratio' => 1]);
        $this->assertEquals('personal', $this->transaction->fresh()->splitType);

        $this->transaction->users()->attach($this->user2, ['split_ratio' => 1]);
        $this->assertEquals('custom', $this->transaction->fresh()->splitType);
    }
}
