<?php
/**
* Users need no explanation, here it is.
*
* @author   Chris Rogers
* @since    1.0.0 (2017-04-27)
*/

namespace App;

use Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\ContentMeta as Messages;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class User extends Authenticatable
{
	use Notifiable;

	/**
	* The user's full name
	*
	* @var 	string
	*/

	public $name;

	/**
	* The user's first name
	*
	* @var 	string
	*/

	public $firstname;
	/**
	* The user's last name
	*
	* @var 	string
	*/
	public $lastname;
	
	/**
	* The user's email
	*
	* @var 	string
	*/
	public $email;

	/**
	* The user's username (typically the email address)
	*
	* @var 	string
	*/
	public $username;

	/**
	* The user's location latititude
	*
	* @var 	string
	*/
	public $lat;

	/**
	* The user's location longitude
	*
	* @var 	string
	*/
	public $lng;
	
	/**
	* The user's checkpoint or level in the game
	*
	* @var 	string
	*/
	public $stage;

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
	protected $fillable = [
		'name', 'firstname', 'lastname', 'email', 'username', 'password', 'lat', 'lng', 'stage'
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/
	protected $hidden = [
		'password', 'remember_token', 'enabled'
	];

	/**
	* Auth check made available to other calling classes
	*
	* @var  boolean 
	*/
	public $isLoggedIn;

	/**
	* Set up variables
	*/
	public function __construct() {

		$this->isLoggedIn = Auth::check();
	}

	/**
	* Roles can have many users. Equally users can have many roles. This is a many-to-many relationship 
	*
	* @return   object  role
	*/
	public function roles() {

		return $this->belongsToMany('App\Role', 'users_roles', 'user_id', 'role_id');
	}

	/**
	* Pages are owned by a single user. This is a one-to-many relationship
	*
	* @return   object  Page
	*/
	public function pages() {

		return $this->hasMany('Page');
	}

	/**
	* Try and log the user in using JSON web tokens
	*
	* @param 	Request $request
	* @return 	array 	$msg
	*/
	public static function authenticate(Request $request) {

		// Grab credentials from the request
		$credentials = $request->only('email', 'password');
		$msg = $title = $token = "";
		$code = 200;
		$type = Messages::TYPES["ContentMeta"];
		$method = "talk";
		try {

			// Attempt to verify the credentials and create a token for the user
			if (!$token = JWTAuth::attempt($credentials)) {
				$msg = "Sorry, don't recognise you. Want to create a new account? Type 'Create an account' if you'd like to save your progress. Or else type 'Continue as guest' to continue as a guest.";
				$code = 401;
				$title = "user not found";
				$type = Messages::TYPES['User'];
				$method = "authenticate";		
			}
		} catch (JWTException $e) {
			
			// Something went wrong whilst attempting to encode the token
			$msg = "Something went wrong. Please try again or say 'Continue as guest' to continue as a guest.";
			$code = 500;
		}

		if ($token) {
			
			// All good so return the token and relay message back
			$usr = json_decode(self::getAuthenticatedUser(), true);
			$msg = sprintf("Welcome back %s.", $usr["name"]);
			$title = "success";
		}
		return Messages::create([
			'content' => __($msg), 
			'key' => "answer", 
			'name' => "success", 
			'title' => $code, 
			'stage' => 0, 
			'type' => $type,
			'method' => $method
		]);
	}

	/**
	* Get the logged in user
	* 
	* @return 	
	*/
	public static function getAuthenticatedUser() {

		$user = JWTAuth::parseToken()->authenticate();
		if (!$user) {
			$msg = "Sorry, don't recognise you. Want to create a new account? Type 'Create an account' if you'd like to save your progress. Or else type 'Continue as guest' to continue as a guest.";
			Messages::create([
				'content' => __($msg),
				'key' => "answer",
				'name' => "user not found", 
				'title' => 401,
				'stage' => 0,
				'type' => Messages::TYPES["User"],
				'method' => 'authenticate'
			]);
		} else {
			return $user;
		}
	}

	/**
	* Check
	*/
	public static function message($msg) {

		$content = __($msg);
		$key = "question";
		$name = "User identification";
		$title = "Who are you";
		$stage = 0;
		return response()->json(
			Messages::create([
				'content' => $content, 
				'key' => $key, 
				'name' => $name, 
				'title' => $title, 
				'stage' => $stage
			]
		));
	}
}
