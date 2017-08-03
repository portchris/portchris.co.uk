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

					// The importer may have overrided IDs, update the users stage back to 1
					$user->stage = 1;
					$user->save();
					throw new \Exception(__("Oh dear, there's been an accident in the office which is in the way of retrieving your records. You'll have to come back again another time to continue. Please sign-in again. Apologies for the inconvenience caused."), 500);
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
				$msg = $User->messageUserAuthorised($user, $nextQ);
			}
		} catch (JWTException $e) {
			
			// Something went wrong whilst attempting to encode the token
			$msg = "Sorry I must've missed something. Please try again or say 'Continue as guest' to continue as a guest.";
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

		return sprintf(__("Your name hasn't appeared in our guestbook before now.%s> If you would like to sign up type 'Create an account'.%s> If you don't want to sign it type 'Continue as guest'.%s> If there has been a mistake and you know you've signed guestbook before then perhaps there has been a mis-typed email or password. Please try again by entering your email address once more."), PHP_EOL . PHP_EOL, PHP_EOL, PHP_EOL);
	}

	/**
	* Message to display when user has been succesfully verified
	*
	* @param 	User 			$user
	* @param 	ContentMeta 	$question
	* @return 	string
	*/
	public function messageUserAuthorised($user, $question) {

		$content = Messages::convertChoiceScriptVariables($question->content, $user);
		return sprintf(__("Ah yes I can see your name in our guestbook. Welcome back to the office %s. Let's pick up where you left off at stage %s of scene \"%s\":%sRemember, if you need any assistance then ask me anything from the \"Helper Commands\" reference at any time. It is located to the right.%s%s"), $user->name, $question->stage, $question->name, PHP_EOL . PHP_EOL, PHP_EOL . PHP_EOL, $content);
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
