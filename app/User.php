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
	* My game so I'm admin :)
	*/
	public const ADMIN_EMAIL = "chris@portchris.co.uk";

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
	protected $fillable = [
		'name', 
		'firstname', 
		'lastname', 
		'email', 
		'username', 
		'password', 
		'lat', 
		'lng', 
		'stage', 
		'conversation'
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/
	protected $hidden = [
		'password', 
		'remember_token', 
		'enabled'
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
		$stage = 1;
		$type = Messages::TYPES["ContentMeta"];
		$method = "talk";
		$key = Messages::KEY_TYPE_ANSWER;
		$title = $code;
		try {

			// Attempt to verify the credentials and create a token for the user
			$User = new self();
			$token = JWTAuth::attempt($credentials);
			$user = (!$token) ? false : $User->getAuthenticatedUser($token);
			if (!$user) {
				$msg = __("Sorry, don't recognise you. Want to create a new account? Type 'Create an account' if you'd like to save your progress. Or else type 'Continue as guest' to continue as a guest.");
				$code = 401;
				$title = "User not found";
				$type = Messages::TYPES['User'];
				$key = Messages::KEY_TYPE_ERROR;
				$method = "authenticate";	
			} else {
				$title = $token;
				$stage = $user->stage;
				$msg = sprintf(__("Welcome back %s. Let's pick up where you left off at level %s. %s"), $user->name, $user->stage, Messages::getNextQuestion($user->stage));
			}
		} catch (JWTException $e) {
			
			// Something went wrong whilst attempting to encode the token
			$msg = "Something went wrong. Please try again or say 'Continue as guest' to continue as a guest.";
			$code = 500;
		}
		return Messages::create([
			'content' => __($msg),
			'key' => $key,
			'name' => $code,
			'title' => $title,
			'stage' => $stage,
			'type' => $type,
			'method' => $method
		]);
	}

	/**
	* Get the logged in user via token
	* 
	* @param 	string 	$token
	* @return 	
	*/
	public function getAuthenticatedUser(string $token) {

		$user = JWTAuth::authenticate($token);
		return $user;
	}

	/**
	* Check
	*/
	public static function message($msg) {

		$content = __($msg);
		$key = Messages::KEY_TYPE_QUESTION;
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
