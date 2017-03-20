<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo() {
        return (Auth::user()->activated)
            ? '/profile'
            : '/user/activation/pending';
    }

    /**
     * Override auth to send user or email depending on what was submitted
     *
     * @return array
     */
    protected function credentials(Request $request) {
        $field = filter_var(
            $request->input($this->username()), FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'username';

        $request->merge([$field => $request->input($this->username())]);

        return $request->only($field, 'password');
    }

    /**
     * Override default email credential
     *
     * @return string
     */
    public function username() {
        return 'login';
    }

    public function loginForm() {
        return view('auth.login');
    }
}
