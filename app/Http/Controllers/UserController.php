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
// use Illuminate\Support\Facades\Input;
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

		$id = 0;
		$msg = $code = "";
		try {
			$user = new User;
			$user->firstname = $request->firstname;
			$user->lastname = $request->lastname;
			$user->name = $request->name;
			$user->email = $request->email;
			$user->username = $request->username;
			$user->password = $request->password; // Password should already be hashed with $this->hashPassword
			$user->lat = $request->lat;
			$user->lng = $request->lng;
			$user->stage = $request->stage;
			$user->conversation = "";
			$user->save();
			$user = User::find($user->id);
			$token = JWTAuth::fromUser($user);
			$q = Messages::getNextQuestion(0);
			if (!$q) {
				throw new \Exception("Error: could not find next question.");
			} 
			$id = $q->id;
			$msg = [
				'id' => $id,
				'content' => sprintf(__("Welcome %s. Let's begin.%s%s"), $user->name, PHP_EOL . PHP_EOL, $q->content),
				'type' => Messages::TYPES['ContentMeta'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => sprintf(__("New user: %s"), $user->id),
				'user_id' => $user->id,
				'title' => $token,
				'method' => 'talk'
			];
			$code = 200;
		} catch(\Illuminate\Database\QueryException $e) {
			$msg = [
				'content' => $e->getMessage(),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ERROR,
				'name' => Messages::KEY_TYPE_ERROR,
				'title' => sprintf(__("Error: %s"), $e->getCode()),
				'method' => 'authenticate'
			];
			$code = 500;
		} catch(\Exception $e) {
			$msg = [
				'content' => $e->getMessage(),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => Messages::KEY_TYPE_ERROR,
				'title' => sprintf(__("Error: %s"), \Illuminate\Http\Response::HTTP_CONFLICT),
				'method' => 'authenticate'
			];
			$code = \Illuminate\Http\Response::HTTP_CONFLICT;
		}
		return response()->json(Messages::create($msg), $code);
	}

	/**
	* Hash a password for subsequent requests, also open to the API
	*
	* @return 	Request 	$request
	*/
	public function hashPassword(Request $request) {

		$p = ["password" => Hash::make($request->input("password"))];
		return response()->json($p);
	}

	/**
	* Log the user out. Simple right.
	*
	* @return 	Response 	JSON
	*/
	public function logOut(Request $request) {

		$msg = "";
		try {
			$token = (JWTAuth::getToken()) ? JWTAuth::getToken()->get() : false;
			$msg = (JWTAuth::invalidate($token)) ? __("Successfully logged off, as if we never met.") : __("Sorry, we couldn't log you out, looks like you're stuck with me for now...");
		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

			// Even if it errors, then JWT Auth has successfully forgotten the token.
			$msg = __("You are now logged out, as if we never met.");
		}
		return response()->json(Messages::create([
			'content' => (strlen((string)$request->input("message")) > 0) ? $request->input("message") : $msg,
			'type' => Messages::TYPES['User'],
			'key' => Messages::KEY_TYPE_ANSWER,
			'name' => 'Log out',
			'title' => 'Log out',
			'method' => 'welcome'
		]));
	}

	/**
	 * Reset the users progress back to thr first stage.
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function reset(Request $request) {
		
		$msg = "Cannot reset, this user does not exist!";
		$r = $this->error($msg);
		$userId = $request->input("user_id");
		if ($userId && (int)$userId > 0 && is_numeric($userId)) {
			User::where("id", $userId)->update(["stage" => 1]);
			$msg = "Users progress successfully reset.";
			$r = response()->json(Messages::create([
				'content' => (strlen((string)$request->input("message")) > 0) ? $request->input("message") : __($msg),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => __('Reset'),
				'title' => __('Reset'),
				'method' => 'welcome',
				'user_id' => $userId
			]));
		}
		return $r;
	}

	/**
	 * Get the admin user (me).
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function getAdminUser(Request $request) {

		$r = [];
		$admin = User::where("email", "=", User::ADMIN_EMAIL)->firstOrFail();
		if (!is_null($admin)) {
			$r = [
				'firstname' => $admin->firstname,
				'lastname' => $admin->lastname,
				'name' => $admin->name,
				'lat' => $admin->lat,
				'lng' => $admin->lng,
				'email' => $admin->email
			];
		}
		return response()->json($r);
	}

	/**
	 * Get nearest timezone information based on geolocation.
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function getNearestTimezone(Request $request) {

		$time_zone = '';
		$tz_distance = 0;
		$cur_lat = $request->lat; 
		$cur_long = $request->lng; 
		$country_code = $request->country_code ?? '';
		$time_format = $request->time_format ?? 'H:i';
		$date_format = $request->date_format ?? 'd/m/Y';
		$r = new \DateTime("now");
		$timezone_ids = ($country_code) ? \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country_code) : \DateTimeZone::listIdentifiers();
		if ($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {

			// Only one identifier?
			if (count($timezone_ids) == 1) {
				$time_zone = $timezone_ids[0];
			} else {
				foreach ($timezone_ids as $timezone_id) {
					$timezone = new \DateTimeZone($timezone_id);
					$location = $timezone->getLocation();
					$tz_lat   = $location['latitude'];
					$tz_long  = $location['longitude'];
					$theta    = $cur_long - $tz_long;
					$distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat))) + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
					$distance = acos($distance);
					$distance = abs(rad2deg($distance));
					if (!$time_zone || $tz_distance > $distance) {
						$time_zone   = $timezone_id;
						$tz_distance = $distance;
					} 
				}
			}
			$r = new \DateTime("now", new \DateTimeZone($time_zone));
		}
		$timestamp = $r->getTimestamp();
		$sunrise = date_sunrise($timestamp, SUNFUNCS_RET_TIMESTAMP, $cur_lat, $cur_long);
		$sunset = date_sunset($timestamp, SUNFUNCS_RET_TIMESTAMP, $cur_lat, $cur_long);
		return response()->json([
			'time' => $r->format($time_format),
			'timestamp' => $timestamp,
			'date' => $r->format($date_format),
			'timezone' => $time_zone,
			'sunrise' => $sunrise,
			'sunset' => $sunset,
			'dark' => ($timestamp <= $sunrise || $timestamp >= $sunset) ? true : false
		]);
	}

	/**
	 * Return user information and next question in the game if the token is valid.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user) {
		
		$r = [];
		JWTAuth::parseToken();
		$token = JWTAuth::getToken()->get();
		$user = JWTAuth::authenticate($token);
		if (!$user) {
			$r = $this->error("Error: user was not authorised from credentials provided.", 500);
		} else {
			$User = new User();
			$userId = $user->id;
			$title = $token;
			$stage = $user->stage;
			$code = 200;
			$nextQ = ($stage == 1) ? Messages::getNextQuestion() : Messages::find($stage);
			if (!$nextQ) {
				$this->error("Error: could not get next question.", 500);
			} else {
				$id = $nextQ->id;
				if ($stage != 1) {
					$responses = Messages::getResponsesToQuestion($nextQ->id);
					if (!is_null($responses)) {
						$nextQ->content .= PHP_EOL;
						foreach ($responses as $r) {
							$nextQ->content .= PHP_EOL . "> " . $r->content;
						}
					} else {
						$nextQ->content .= PHP_EOL . Messages::getFinalMessage();
					}
				}
				$msg = $User->messageUserAuthorised($user, $nextQ);
				$r = Messages::create([
					'id' => $id,
					'content' => $msg,
					'key' => Messages::KEY_TYPE_QUESTION,
					'name' => sprintf(__("Response: %s"), $code),
					'title' => $title,
					'stage' => $stage,
					'user_id' => $userId
				]);
			}
		}

		return $r;
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
			$code = ($e->getCode() !== 0) ? $e->getCode() : 401;
			$msg = Messages::create([
				'content' => __($e->getMessage()) . " " . $e->getFile() . "::" . $e->getLine(), 
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

	/**
	* Silly, the user has opted out of creating an account. Oh well, they can still play on a guest token.
	*
	* @param 	Request 	$request
	* @return 	JSON
	*/
	public function createGuestToken(Request $request) {

		$id = 0;
		$cookie = false;
		$msg = $key = $title = $name = $code = $stage = $type = $method = "";
		try {
			$claims = $request->all();	
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
				$q = Messages::getNextQuestion(0);
				if (!$q) {
					throw new \Exception("Error: could not find next question.");
				}
				$id = $q->id;
				$msg = sprintf(__("Welcome %s to the game. Let's begin. %s"), $claims["username"], $q->content);
				$code = 200;
				$name = "success";
				$title = $token->get();
				$key = Messages::KEY_TYPE_ANSWER;
				$stage = 1;
				$type = Messages::TYPES['ContentMeta'];
				$method = "talk";
				session(['key' => $token->get()]);
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
			'id' => $id,
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
	* Create JSON error response 
	*
	* @return 	JSON object 	$response
	* @since 	1.0.0
	*/
	public function error($msg, $errCode = 500) {

		return response()->json(Messages::create([
			'content' => __($msg),
			'key' => Messages::KEY_TYPE_ERROR,
			'name' => Messages::RESPONSE_ERROR,
			'title' => Messages::RESPONSE_ERROR,
			'stage' => 0,
			0
		]), $errCode);
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
	// public function identify($data) {
		
	// 	$return = User::message("Welcome, with whom is it I speak?");
	// 	$username = $data["username"] ?? false;
	// 	$password = $data["password"] ?? false;
	// 	if (Auth::check()) {
	// 		$return = Auth::user();		
	// 	} else if ($username && $password) {
	// 		$return = Auth::attempt([
	// 			'username' => $username, 
	// 			'password' => $password
	// 		]);
	// 	}
	// 	return $return;
	// }
}
