<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use App\User;
use App\ContentMeta as Messages;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\AuthenticateUserRequest;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	use RegistersUsers;

	/**
	* Request object
	* @var 	Request 	$request
	*/
	private $request;

	/**
	* Since I cannot figure out why Reqest isn't being sent, get it manually on construct
	*/
	public function __construct() {

		$this->request = app('request');
		$this->middleware('guest');
	}

	/**
	 * Try and identify the user and display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		// 
	}

	/**
	 * Show the form for creating a new User. Should not be accessible
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {


	}

	/**
	 * Store a newly created User in storage.
	 *
	 * @param  \App\Http\Requests\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreUserRequest $request) {

		$msg = $code = "";
		try {
			$user = new User;
			$user->firstname = $request->firstname;
			$user->lastname = $request->lastname;
			$user->name = $request->name;
			$user->email = $request->email;
			$user->username = $request->username;
			$user->password = Hash::make($request->password);
			$user->lat = $request->lat;
			$user->lng = $request->lng;
			$user->stage = $request->stage;
			$user->conversation = "";
			$user->save();
			$user = User::find($user->id);
			$token = JWTAuth::fromUser($user);
			$msg = [
				'content' => sprintf(__("Welcome %s. Let's begin. %s"), $user->name, Messages::getNextQuestion($user->stage)),
				'type' => Messages::TYPES['ContentMeta'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => 'error',
				'title' => $token,
				'method' => 'talk'
			];
			$code = 200;
		} catch(\Illuminate\Database\QueryException $e) {
			$msg = [
				'content' => $e->getMessage(),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => 'error',
				'title' => 'Error, code: ' . $e->getCode(),
				'method' => 'authenticate'
			];
			$code = 500;
		} catch(\Exception $e) {
			$msg = [
				'content' => $e->getMessage(),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => 'error',
				'title' => 'Error, code: ' . \Illuminate\Http\Response::HTTP_CONFLICT,
				'method' => 'authenticate'
			];
			$code = \Illuminate\Http\Response::HTTP_CONFLICT;
		}
		return response()->json(Messages::create($msg), $code);
	}

	/**
	* Hash a password, also open to the API
	*
	* @return 	string 	Hashed password
	*/
	public function hasPassword($password) {

		return Hash::make($password);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user) {
		
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function edit(User $user) {
		
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user) {
		
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user) {
		
		//
	}

	/**
	* Attempt to log the user in using JSON Web Tokens. 
	* Try catch custom exceptions as opposed to leaving it up to app/Exceptions/Handler
	* Note: using Illuminate\Support\Facades\Input despite being deprecated as I CANNOT figure out Request!!
	*
	* @return 	JSON 	$msg
	*/
	public function authenticate(AuthenticateUserRequest $request) {
		
		$msg = "";
		$code = 200;
		$method = "authenticate";
		$type = Messages::TYPES["User"];
		try {
			$msg = User::authenticate($request);
			// $request = Request::create('api/user/authenticate', 'POST', $data);
			// $msg = Route::dispatch($request);
			// $msg = ($msg->original) ? $msg->original : $msg;
		} catch (\Exception $e) {
			$code = $e->getCode();
			$msg = Messages::create([
				'content' => __($e->getMessage()), 
				'key' => Messages::KEY_TYPE_ANSWER, 
				'name' => Messages::RESPONSE_ERROR,
				'title' => "Error, code: " . $code,  
				'code' => $code, 
				'stage' => 0,
				'type' => $type,
				'method' => "authenticate"
			]);
		}

		// The token is valid and we have found the user via the sub claim
		// var_dump(response()->json(compact('user'))); die;
		return response()->json($msg, $code);
	}

	/**
	* Get a validator for an incoming registration request.
	*
	* @param  array  $data
	* @return \Illuminate\Contracts\Validation\Validator
	*/
    protected function validator(array $data) {

        return Validator::make($data, [
            'username' => 'required|max:20',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

	public function createGuestToken() {

		$msg = $key = $title = $name = $code = $stage = $type = $method = "";
		try {
			$claims = Input::all();	
			$payload = $this->createJWTPayload(
				$claims,
				$claims["username"],
				time(),
				strtotime("+1 day"),
				time(),
				Route::current()->getName()
			);
			$token = JWTAuth::encode($payload);
			if (!$token) {
				$msg = __("Could not create guest account please try again");
				$code = 500;
				$name = "error";
				$title = "NO TOKEN";
				$key = Messages::KEY_TYPE_ANSWER;
				$stage = 0;
				$type = Messages::TYPES['User'];
				$method = "authenticate";
			} else {
				$this->request->session()->put('key', $token);
				$msg = sprintf(__("Welcome %s to the game. Let's begin. %s"), $claims["username"], Messages::getNextQuestion(0));
				$code = 200;
				$name = "success";
				$title = $token->get();
				$key = Messages::KEY_TYPE_ANSWER;
				$stage = 1;
				$type = Messages::TYPES['ContentMeta'];
				$method = "talk";
			}
		} catch (\Exception $e) {
			$msg = __($e->getMessage());
			$code = ($e->getCode() > 0) ? $e->getCode() : 500;
			$name = "error";
			$title = "NO TOKEN";
			$key = Messages::KEY_TYPE_ANSWER;
			$stage = 0;
			$type = Messages::TYPES['User'];
			$method = "authenticate";
		}
		$m = Messages::create([
			'content' => $msg,  
			'key' => Messages::KEY_TYPE_ERROR, 
			'name' => $name,
			'title' => $title,
			'stage' => $stage, 
			'type' => $type,
			'method' => $method
		]);
		return response()->json($m, $code);
	}

	/**
	* Create all required fields for JSON Web Token 
	* 
	* @param 	array 		$cus Custom extra payloads
	* @param 	string 		$sub Subject - This holds the identifier for the token (defaults to user id)
	* @param 	timestamp 	$iat Issued At - When the token was issued (unix timestamp)
	* @param  	timestamp 	$exp Expiry - The token expiry date (unix timestamp)
	* @param  	timestamp 	$nbf Not Before - The earliest point in time that the token can be used (unix timestamp)
	* @param  	string 		$iss Issuer - The issuer of the token (defaults to the request url)
	* @param  	string 		$jti JWT Id - A unique identifier for the token (md5 of the sub and iat claims)
	* @param  	string 		$aud Audience - The intended audience for the token (not required by default)
	* @return 	array 		payload
	*/
	private function createJWTPayload($cus, $sub, $iat, $exp, $nbf, $iss, $jti = "Lucy Wood", $aud = "") {

		$claims = [
			'sub' => $sub,
			'iat' => $iat,
			'exp' => $exp,
			'nbf' => $nbf,
			'iss' => $iss,
			'jti' => $jti,
			'aud' => $aud
		];
		$claims = array_merge($claims, $cus);
		return JWTFactory::make($claims);
	}

	/**
	* Get the logged in user
	*
	* @return 	JSON response of user
	*/
	// public function getAuthenticatedUser() {

	// 	$msg = "";
	// 	$code = 200;
	// 	try {
	// 		$msg = User::authenticate();
	// 	} catch (Exception $e) {
	// 		$code = $e->getCode();
	// 		$msg = Messages::create([
	// 			'content' => __($e->getMessage()), 
	// 			'key' => "answer", 
	// 			'name' => "error", 
	// 			'title' => $e->getCode(), 
	// 			'stage' => 0,
	// 			'type' => Messages::TYPES["User"],
	// 			'method' => "authenticate"
	// 		]);
	// 	}

	// 	// The token is valid and we have found the user via the sub claim
	// 	var_dump(response()->json(compact('user'))); die;
	// 	return response()->json($msg, $code);
	// }

	/**
	 * Attempt to identify the user.
	 *
	 * @param  array 	$data
	 * @return \Illuminate\Http\Response 	JSON
	 */
	public function identify($data) {
		
		$return = User::message("Welcome, with whom is it I speak?");
		$username = $data["username"] ?? false;
		$password = $data["password"] ?? false;
		if (Auth::check()) {
			$return = Auth::user();		
		} else if ($username && $password) {
			$return = Auth::attempt([
				'username' => $username, 
				'password' => $password
			]);
		}
		return $return;
	}
}
