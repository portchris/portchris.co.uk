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
	// public $isLoggedIn;

	/**
	* Set up variables
	*/
	public function __construct() {

		// $this->isLoggedIn = Auth::check();
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
		$id = $user_id = $code = $stage = $title = 0;
		$type = Messages::TYPES["ContentMeta"];
		$method = "talk";
		$key = Messages::KEY_TYPE_ANSWER;
		try {

			// Attempt to verify the credentials and create a token for the user
			$User = new self();
			$token = JWTAuth::attempt($credentials);
			$user = (!$token) ? false : $User->getAuthenticatedUser($token);
			if (!$user) {
				$msg = $User->messageUserNotFound();
				$code = 401;
				$title = __("User not found");
				$type = Messages::TYPES['User'];
				$key = Messages::KEY_TYPE_QUESTION;
				$method = "authenticate";	
			} else {
				$user_id = $user->id;
				$title = $token;
				$stage = $user->stage;
				$code = 200;
				$nextQ = ($stage == 1) ? Messages::getNextQuestion() : Messages::find($stage);
				if (!$nextQ) {
					throw new \Exception(__("Error: could not get next question."), 500);
				}
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
				$msg = $User->messageUserAuthorised($user->name, $nextQ->stage, $nextQ->content);
			}
		} catch (JWTException $e) {
			
			// Something went wrong whilst attempting to encode the token
			$msg = "Something went wrong. Please try again or say 'Continue as guest' to continue as a guest.";
			$code = 500;
		}
		return Messages::create([
			'id' => $id,
			'content' => __($msg),
			'key' => $key,
			'name' => sprintf(__("Response: %s"), $code),
			'title' => $title,
			'stage' => $stage,
			'user_id' => $user_id,
			'type' => $type,
			'method' => $method
		]);
	}

	/**
	* Message to display when user is not verified
	*
	* @return 	string
	*/
	public function messageUserNotFound() {

		return sprintf(__("Sorry, I don't recognise you. You must be new around here:%s> If you want to create a new account? Type 'Create an account'. This way you I track your progress for next time.%s> Don't want me to track your progress? No biggie, type 'Continue as guest' to continue as a guest.%s> Or perhaps you simply mis-typed your email address or password try entering another address if this is the case."), PHP_EOL . PHP_EOL, PHP_EOL, PHP_EOL);
	}

	/**
	* Message to display when user has been succesfully verified
	*
	* @return 	string
	*/
	public function messageUserAuthorised($name, $stage, $content) {

		return sprintf(__("Welcome back %s. Let's pick up where you left off at level %s.%s%s"), $name, $stage, PHP_EOL, $content);
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
}
