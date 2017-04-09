<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class UserController extends Controller {

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
}
