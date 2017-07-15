<?php namespace App\Http\Controllers;

use App\Photo;
use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Response;
use Validator;
use Image;

class UserController extends Controller {

	public function info() {
		$user = Auth::user();

        return collect([
        	'first_name' => $user->first_name,
        	'last_name' => $user->last_name,
			'about' => $user->about
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
        	'last_name' => $request->last_name,
			'about' => $request->about
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

	public function requests() {
		return Auth::user()->allRequests;
	}

	public function uploadPhoto(User $user, Request $request) {

		$file = $request->file('photo')->store('photo');

		// Get file name to construct thumbnail path
		preg_match('/^[\w:\-\/]*\/([\w-]*)\.(\w{1,5})$/', $file, $matches);

		// Create thumbnail
		$image = Image::make('storage/' . $file)
			->resize(150, 150, function ($constraint) {
			    $constraint->aspectRatio();
			})
			->save('storage/photo/' . $matches[1] . '-thumb.' . $matches[2]);

		// Save photo path for user
		Photo::create([
			'path' => $file,
			'related_id' => Auth::id(),
			'related_type' => 'App\User'
		]);

		return back();
	}

    protected function validator(array $data) {
        return Validator::make($data, [
            'first_name' => 'required|max:30',
            'last_name'  => 'required|max:30',
			'about' => 'max:128'
        ]);
    }
}
