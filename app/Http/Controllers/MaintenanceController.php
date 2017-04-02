<?php namespace App\Http\Controllers;

use App\Hashtag;
use App\Transaction;
use Auth;
use Exception;
use Illuminate\Http\Request;

class MaintenanceController extends Controller {

	public function loadHashtags(){
		if (Hashtag::count() > 0) {
			throw new Exception('Hashtag table contains data.');
		}

		$transactions = Transaction::select('hashtags', 'id')
		->where('hashtags', '!=', null)
		->get()
		->each(function($item){
			$id = $item->id;
			$hashtags = collect(explode(',', $item->hashtags));

			$hashtags->each(function($hashtag) use ($id){
				$hashtag = Hashtag::firstOrCreate([ 'tag' => $hashtag ]);
				$hashtag->transactions()->attach($id);
			});
		});

		return 'Hashtags populated.';
	}
}