<?php namespace App\Http\Controllers;

use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Response;
use Validator;

class UserController extends Controller {


	public function info() {
        return collect([
        	'first_name' => Auth::user()->first_name,
        	'last_name' => Auth::user()->last_name
        ]);
    }

    public function update(Request $request) {

    	$validator = $this->validator($request->all());

    	if ($validator->fails()) {
    		return Response::json($validator->errors(), 422);
    	}

    	$user = Auth::user();

    	$user->update([
    		'first_name' => $request->first_name,
        	'last_name' => $request->last_name
    	]);
    }

    public function search(Request $request) {

    	$this->validate($request, [
    		'input' => 'required'
    	]);

    	$results = DB::select("
    		SELECT first_name, last_name, username
			FROM users
			WHERE (
				(first_name LIKE :input1 OR last_name LIKE :input2)
				OR (CONCAT(first_name, ' ', last_name) LIKE :input3)
			)
			AND activated = true
			ORDER BY last_name, first_name
			LIMIT 5
			", [
				'input1' => "%$request->input%",
				'input2' => "%$request->input%",
				'input3' => "%$request->input%"
			]
		);

    	return $results;
    }

    protected function validator(array $data) {
        return Validator::make($data, [
            'first_name' => 'required|max:30',
            'last_name'  => 'required|max:30'
        ]);
    }
}
