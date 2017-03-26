<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Trip;
use \App\Transaction;
use \App\TransactionUser;

use DB;
use Auth;
use Hash;
use Response;

class TransactionController extends Controller {

   	public function byTrip(Trip $trip, Transaction $transaction) {
   		$transaction = Transaction::where([
   			'id' => $transaction->id,
   			'trip_id' => $trip->id
   		])->firstOrFail();

   		$travelers = DB::select('
	   		SELECT * FROM (
	   			SELECT u.id AS join_on, u.id AS id,
	   				CONCAT(u.first_name, " ", u.last_name) AS full_name
	   			FROM users u
	   			JOIN trip_user tu ON u.id = tu.user_id
	   			JOIN trips t      ON tu.trip_id = t.id
	   			WHERE t.id = :trip_id
	   		) tb1
	   		LEFT JOIN (
	   			SELECT user_id AS join_on, split_ratio, count(*) AS is_spender
	   			FROM transaction_user
	   			WHERE transaction_id = :transaction_id
	   			GROUP BY user_id, split_ratio
	   		) tb2
	   		ON tb1.join_on = tb2.join_on
   			', [
   				'trip_id'        => $trip->id,
   				'transaction_id' => $transaction->id
   			]);

   		return [
   			'transaction' => $transaction,
   			'travelers'   => $travelers
   		];
   	}

   	public function store(Request $request, Trip $trip) {
   		$this->validateTransaction();

   		return $transaction = DB::transaction(function() use ($request, $trip) {

   			$hashtags = (empty($request->hashtags['items']))
            	? null
            	: implode(',', request('hashtags')['items']);

           	// Update transaction data
            $transaction = Transaction::create([
            	'trip_id' => $trip->id,
                'amount' => request('amount'),
                'date' => request('date'),
                'description' => request('description'),
                'hashtags' => $hashtags,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // Filter updated travelers to get spenders
            $spenders = collect($request->travelers)->filter(function($spender){
	   			return $spender['is_spender'] && !empty($spender['split_ratio']);
	   		});

            // Update or create spenders
            foreach ($spenders as $spender) {
            	TransactionUser::create([
            		'transaction_id' => $transaction->id,
            		'user_id' => $spender['id'],
            		'split_ratio' => $spender['split_ratio']
            	]);
            }

           	return $transaction;
        });
   	}

   	public function update(Request $request, Trip $trip, Transaction $transaction) {
   		$this->validateTransaction();

        // TODO make hashtags table

   		return $transaction = DB::transaction(function() use ($request, $transaction) {

   			$hashtags = (empty($request->hashtags['items']))
            	? null
            	: implode(',', request('hashtags')['items']);

           	// Update transaction data
            $transaction->update([
                'date' => request('date'),
                'amount' => request('amount'),
                'description' => request('description'),
                'hashtags' => $hashtags,
                'updated_by' => Auth::user()->id
            ]);

            // Filter updated travelers to get spenders
            $updatedSpenders = collect($request->travelers)->filter(function($spender){
	   			return $spender['is_spender'] && !empty($spender['split_ratio']);
	   		});

            // Delete orphaned spenders
	   		TransactionUser::where('transaction_id', $transaction->id)
	   			->whereNotIn('user_id', $updatedSpenders->pluck('id'))
	   			->delete();

            // Update or create spenders
            foreach ($updatedSpenders as $spender) {
            	TransactionUser::updateOrCreate([
            		'transaction_id' => $transaction->id,
            		'user_id' => $spender['id'],
            		'split_ratio' => $spender['split_ratio']
            	]);
            }

           	return $transaction;
        });
   	}

   	public function destroy(Request $request, Trip $trip, Transaction $transaction) {
        // Verify user's password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return Response::json([
                'password' => ['The password you entered is incorrect.']
            ], 422);
        }

    	$transaction->delete();

   		return ['success' => true];
   	}

    public function validateTransaction() {
        return $this->validate(request(), [
            'date' => 'required|date_format:Y-n-j',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'hashtags.items.*' => 'regex:/^[^,\s]+$/',
            'travelers.*.split_ratio' => ['nullable', 'regex:/(^\d*\.?\d+$)|(^\d+\.?\d*$)/']
        ]);
    }
}
