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
	public function __construct()
	{

		$this->request = app('request');
	}

	/**
	 * Try and identify the user and display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// 
	}

	/**
	 * Show the form for creating a new User. Should not be accessible
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
	}

	/**
	 * Store a newly created User in storage.
	 *
	 * @param  \App\Http\Requests\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreUserRequest $request)
	{

		$id = 0;
		$msg = $code = "";
		try {
			$user = new User;
			$user->firstname = $request->json('firstname');
			$user->lastname = $request->json('lastname');
			$user->name = $request->json('name');
			$user->email = $request->json('email');
			$user->username = $request->json('username');
			$user->password = $request->json('password'); // Password should already be hashed with $this->hashPassword
			$user->lat = $request->json('lat');
			$user->lng = $request->json('lng');
			$user->stage = $request->json('stage');
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
				'content' => sprintf(__("All done %s. You have successfully registered in our guestbook! Let's begin.%s%s%sNote: If you ever get stuck, you can refer to the \"Helper Commands\" box - you can type any of those commands in at any point."), $user->name, PHP_EOL . PHP_EOL, $q->content, PHP_EOL . PHP_EOL),
				'type' => Messages::TYPES['ContentMeta'],
				'key' => Messages::KEY_TYPE_ANSWER,
				'name' => sprintf(__("New user: %s"), $user->id),
				'user_id' => $user->id,
				'title' => $token,
				'method' => 'talk'
			];
			$code = 200;
		} catch (\Illuminate\Database\QueryException $e) {
			$msg = [
				'content' => $e->getMessage(),
				'type' => Messages::TYPES['User'],
				'key' => Messages::KEY_TYPE_ERROR,
				'name' => Messages::KEY_TYPE_ERROR,
				'title' => sprintf(__("Error: %s"), $e->getCode()),
				'method' => 'authenticate'
			];
			$code = 500;
		} catch (\Exception $e) {
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
	public function hashPassword(Request $request)
	{
		$user = new User();
		$p = ["password" => $user->setPasswordAttribute($request->json("password"))];
		return response()->json($p);
	}

	/**
	 * Log the user out. Simple right.
	 *
	 * @return 	Response 	JSON
	 */
	public function logOut(Request $request)
	{
		$msg = "";
		try {
			$token = (JWTAuth::getToken()) ? JWTAuth::getToken()->get() : false;
			$msg = (JWTAuth::invalidate($token)) ? __("Done! Successfully signed out of our guestbook.") : __("Sorry, we couldn't log you out, looks like you're stuck with us for the time being. It's raining outside anyway!");
		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

			// Even if it errors, then JWT Auth has successfully forgotten the token.
			$msg = __("Done! Successfully signed out of our guestbook.");
		}
		return response()->json(Messages::create([
			'content' => (strlen((string) $request->json("message")) > 0) ? $request->json("message") : $msg,
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
	public function reset(Request $request)
	{

		$msg = "Cannot reset, your name does not appear in the visitors guestbook!";
		$r = $this->error($msg);
		$userId = $request->json("user_id");
		if ($userId && (int) $userId > 0 && is_numeric($userId)) {
			User::where("id", $userId)->update(["stage" => 1]);
			$msg = "Okay, you've been signed out of our guestbook and your progress has been reset back to stage 1.";
			$r = response()->json(Messages::create([
				'content' => (strlen((string) $request->json("message")) > 0) ? $request->json("message") : __($msg),
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
	 * Remove the user.
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function remove(Request $request)
	{

		$msg = "Cannot remove you, your name does not appear in the visitors guestbook!";
		$r = $this->error($msg);
		$userId = $request->json("user_id");
		if ($userId && (int) $userId > 0 && is_numeric($userId)) {
			try {
				$deleted = User::where("id", $userId)->delete();
				if ($deleted) {
					$msg = "Okay, we have removed your name from the guestbook. As if we never met... :(";
					$r = response()->json(Messages::create([
						'content' => (strlen((string) $request->json("message")) > 0) ? $request->json("message") : __($msg),
						'type' => Messages::TYPES['User'],
						'key' => Messages::KEY_TYPE_ANSWER,
						'name' => __('Reset'),
						'title' => __('Reset'),
						'method' => 'welcome',
						'user_id' => $userId
					]));
				} else {
					throw new \Exception($msg, 1);
				}
			} catch (Exception $e) {
				$r = response()->json(Messages::create([
					'content' => (strlen((string) $request->json("message")) > 0) ? $request->json("message") : __($msg),
					'type' => Messages::TYPES['User'],
					'key' => Messages::KEY_TYPE_ERROR,
					'name' => __('Reset'),
					'title' => __('Reset'),
					'method' => 'welcome',
					'user_id' => $userId
				]), 500);
			}
		}
		return $r;
	}

	/**
	 * Get the admin user (me).
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function getAdminUser(Request $request)
	{

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
	public function getNearestTimezone(Request $request)
	{

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
	public function show(User $user)
	{

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
	public function edit(User $user)
	{

		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user)
	{

		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user)
	{

		//
	}

	/**
	 * Attempt to log the user in using JSON Web Tokens. 
	 * Try catch custom exceptions as opposed to leaving it up to app/Exceptions/Handler
	 *
	 * @return 	JSON 	$msg
	 */
	public function authenticate(AuthenticateUserRequest $request)
	{

		$msg = "";
		$code = 200;
		$method = "authenticate";
		$type = Messages::TYPES["User"];
		try {
			$msg = User::authenticate($request);
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
	protected function validator(array $data)
	{

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
	public function createGuestToken(Request $request)
	{
		$t = time();
		$id = 0;
		// $cookie = false;
		$msg = $key = $title = $name = $code = $stage = $type = $method = "";
		try {
			$name = $request->json('username');
			$claims = $request->json('password');
			$user = new User;
			$user->firstname = $name;
			$user->lastname = "";
			$user->name = $name;
			$user->email = "null@null.com";
			$user->username = $name;
			$user->password = $claims;
			$user->lat = 0;
			$user->lng = 0;
			$user->stage = 0;
			$user->conversation = "";
			$token = JWTAuth::fromUser($user);
			// $payload = $this->createJWTPayload(
			// 	$claims,
			// 	$name,
			// 	time(),
			// 	strtotime("+1 day"),
			// 	time(),
			// 	Route::current()->getName()
			// );
			// $token = JWTAuth::encode($payload);
			// JWTAuth::parseToken();
			// $token = JWTAuth::getToken()->get();

			if (!$token) {
				$msg = __("Could not create guest record please try again");
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
				$msg = sprintf(__("Okay %s! Now let's begin. %s%sNote: If you ever get stuck, you can refer to the \"Helper Commands\" box - you can type any of those commands in at any point."), $name, $q->content, PHP_EOL . PHP_EOL);
				$code = 200;
				$name = "success";
				$title = $token;
				$key = Messages::KEY_TYPE_QUESTION;
				$stage = 1;
				$type = Messages::TYPES['ContentMeta'];
				$method = "talk";
				session(['key' => $token]);
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
			'key' => $key,
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
	private function createJWTPayload($cus, $sub, $iat, $exp, $nbf, $iss, $jti = "Lucy Rogers", $aud = "")
	{
		$claims = [
			'sub' => $sub,
			'iat' => $iat,
			'exp' => $exp,
			'nbf' => $nbf,
			'iss' => $iss,
			'jti' => $jti,
			'aud' => $aud
		];
		// $claims = array_merge($claims, $cus);
		$payload = JWTFactory::make($claims);
		return $payload;
	}

	/**
	 * @param int $length
	 * @return string
	 */
	public static function quickRandom($length = 16)
	{
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
	}

	/**
	 * Create JSON error response 
	 *
	 * @return 	JSON object 	$response
	 * @since 	1.0.0
	 */
	public function error($msg, $errCode = 500)
	{

		return response()->json(Messages::create([
			'content' => __($msg),
			'key' => Messages::KEY_TYPE_ERROR,
			'name' => Messages::RESPONSE_ERROR,
			'title' => Messages::RESPONSE_ERROR,
			'stage' => 0,
			0
		]), $errCode);
	}
}
