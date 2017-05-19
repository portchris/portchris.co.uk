<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
	 * Where to redirect users after login.
	 *
	 * @var string
	 */
	protected $redirectTo = '/home';

	/**
	* Since all functionality is displayed as a message stream, we need to also make this response so.
	*
	* @var 	array 
	*/
	public $message;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->middleware('guest', ['except' => 'logout']);
	}

	public function index() {

		// The user is logged in...
		if (Auth::check()) {
			return "LOGGED IN";
		} else {
			return "NOT LOGGED IN";
		}
	}

	public function username() {
		
		return 'username';
	}
}
