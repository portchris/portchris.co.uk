<?php
/**
* The heart of the text based adventure
*
* @author 	Chris Rogers
* @since 	1.0.0 <2017-05-01>
*/
namespace App\Http\Controllers;

use App\User;
use App\Page;
use App\ContentMeta as Messages;
// use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessageRequest;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MessageStreamController extends Controller
{

	public $user;
	private $is_guest;
	private $token;
	private $request;

	public function __construct() {

		// Require the user to be verified first
		// $this->middleware('jwt-auth');
		$this->request = app('request');
		$this->is_guest = false;
		try { 
			JWTAuth::parseToken();
			$this->token = JWTAuth::getToken();
			$this->user = JWTAuth::toUser($this->token);
		} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
			if (!$this->isVerifiedGuest()) {
				throw new \Tymon\JWTAuth\Exceptions\TokenExpiredException($e->getMessage(), $e->getStatusCode());
			}
		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
			if (!$this->isVerifiedGuest()) {
				throw new \Tymon\JWTAuth\Exceptions\JWTException($e->getMessage(), $e->getStatusCode());
			}
		} catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
			if (!$this->isVerifiedGuest()) {
				throw new \Tymon\JWTAuth\Exceptions\TokenInvalidException($e->getMessage(), $e->getStatusCode());
			}
		}
	}

	/**
	* Get current user, progress and current question
	*
	* @since 	1.0.0
	* @return 	array 	User | Question | Stage
	*/
	public function index() {

		// Require the user to be logged in first
		$UserController = new UserController();

		// The user is logged in...
		if (Auth::check()) {
			return Messages::all();
		} else {
			return $UserController->identify();
		}
		// return Messages::all();
	}

	/**
	* Get the message by ID (could be question or answer)
	*
	* @return 	int 	$question_id
	* @since 	1.0.0
	*/
	public function show($question_id) {
		
		return Messages::find($question_id) ?? $this->error("No messages found!");
	}

	/**
	* Submit answer to question the current question based on progress (stage)
	*
	* @param 	string 	$answer 	
	* @param 	int 		$question_id
	* @return 	string 	$question
	* @since 	1.0.0
	*/
	public function store(StoreMessageRequest $data) {

		$return = $this->error("Could not submit answer");
		$k = $data['key'] ?? false;
		$c = $data['content'] ?? false;
		$u = (int)$data['user_id'] ?? false;
		$p = (int)$data['page_id'] ?? false;
		$s = (int)$data['stage'] ?? 0;
		$n = $data['name'] ?? '';
		$t = $data['title'] ?? $n;
		$lId = $data['id_linked_content_meta'] ?? '';

		// Save message for existing users
		if (!$this->is_guest && !empty((array)$this->user)) {
			$sanitizedData = [
				'name' => $n,
				'id_linked_content_meta' => $lId, // This is how portchris can respond
				'title' => $t,
				'key' => $k,
				'stage' => $s,
				'content'=> $c,
				'user_id' => $u,
				'page_id' => $p
			];
			$message = Messages::create($sanitizedData);
			if (!$message->save()) {
				$sanitizedData['content'] = __('Error: could not save message'); 
				return response()->json(Messages::create([
					$sanitizedData
				]), 500);
			}
		}

		// Respond to users message
		$return = $this->doRespond($data);
		return $return;
	}

	/**
	* The user has submitted
	*
	* @since 	1.0.0
	*/
	protected function doRespond($data) {

		$return = "";
		extract($data);
		$response = Messages::respond($lId, $c);
		if (!empty((array)$response)) {
			$return = response()->json(Messages::create([
				'content' => $response->getAnswer(),
				'question' => $response->getQuestion(),
				'message' => $response->getMessage(),
				'response' => $response->getResponse(),
				'stage' => (int)$stage + 1,
				'name' => $name,
				'id_linked_content_meta' => $id_linked_content_meta,
				'title' => __("Response to: ") . $response->getMessage(),
				'key' => "answer",
				'user_id' => $user_id,
				'page_id' => $page_id
			]), 200);
		} else {
			$return = response()->json(Messages::create([
				'content' => __("No response, please try again later"),
				'stage' => $stage,
				'name' => $name,
				'id_linked_content_meta' => $id_linked_content_meta,
				'title' => $title,
				'key' => "answer",
				'user_id' => $user_id,
				'page_id' => $page_id
			]), 500);
		}
		return $return;
	}

	/**
	* Get users progress
	*
	* @return 	int $stage
	* @since 	1.0.0
	*/
	protected function stage() {

	}

	/**
	* Set users progress
	*
	* @return 	int $stage
	* @since 	1.0.0
	*/
	protected function setStage() {

	}

	/**
	* Attempt to try and retreive the current user from session or database 
	*
	* @return 	object 	$user
	* @since 	1.0.0
	*/
	private function getUser() {
		
	}

	/**
	* Create JSON error response 
	*
	* @return 	JSON object 	$response
	* @since 	1.0.0
	*/
	public function error($msg) {

		return response()->json(Messages::create([
			'content' => __($msg),
			'key' => Messages::RESPONSE_ERROR,
			'name' => Messages::RESPONSE_ERROR,
			'title' => Messages::RESPONSE_ERROR,
			'' => Messages::RESPONSE_ERROR,
			'stage' => 0,
			0
		]), 500);
	}

	/**
	* If the user typed 'Continue as guest' then a key should be present, else they are not verified
	*
	* @return 	boolean
	*/
	private function isVerifiedGuest() {

		$t = (string)$this->request->input('token');
		$k = (string)$this->request->session()->get('key');
		$this->is_guest = (strlen($t) > 0 && strlen($k) > 0 && $t === $k) ? true : false;
		return $this->is_guest;
	}
}
