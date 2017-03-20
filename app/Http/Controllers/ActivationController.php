<?php namespace App\Http\Controllers;

use App\ActivationService;
use Auth;
use Illuminate\Http\Request;
use Session;

class ActivationController extends Controller {

	protected $activationService;

	protected $redirectPath = '/profile';

	public function __construct(ActivationService $activationService) {
        $this->activationService = $activationService;
    }

	public function pending() {
		return (Auth::user()->activated)
			? redirect('/profile')
			: view('auth.activationPending');
	}

	public function resend() {
		$resend = $this->activationService->sendActivationMail(Auth::user());
		$response = $resend['response'];
		Session::flash('response', $response);

		return redirect('/user/activation/pending');
	}

	public function activateUser($token) {
        if ($user = $this->activationService->activateUser($token)) {
            auth()->login($user);
            return redirect($this->redirectPath);
        }
        abort(404);
    }
}