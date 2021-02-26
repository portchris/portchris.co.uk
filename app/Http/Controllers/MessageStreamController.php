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
use App\Message;
use App\ContentMeta as Messages;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessageRequest;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class MessageStreamController extends Controller
{

	public $user;
	private $is_guest;
	private $token;
	private $request;

	public function __construct()
	{

		// Require the user to be verified first
		// $this->middleware('jwt-auth');
		// $this->middleware('web');
		$this->request = app('request');
		$this->is_guest = false;
		if (!\App::runningInConsole()) {
			try {
				JWTAuth::parseToken();
				$this->token = JWTAuth::getToken()->get();
				$this->user = JWTAuth::authenticate($this->token);
				if (!$this->user && $this->isVerifiedGuest() && $this->request->input('user')) {
					$this->setUser((array) $this->request->input('user'));
				}
			} catch (TokenExpiredException $e) {
				if (!$this->isVerifiedGuest()) {
					throw new TokenExpiredException($e->getMessage(), $e->getCode());
				}
			} catch (JWTException $e) {
				if (!$this->isVerifiedGuest()) {
					throw new JWTException($e->getMessage(), $e->getCode());
				}
			} catch (TokenInvalidException $e) {
				if (!$this->isVerifiedGuest()) {
					throw new TokenInvalidException($e->getMessage(), $e->getCode());
				}
			}
		}
	}

	/**
	 * Get current user, progress and current question
	 *
	 * @since 	1.0.0
	 * @return 	array 	User | Question | Stage
	 */
	public function index()
	{

		$q = Messages::getNextQuestion($this->user->stage);
		return response()->json(Messages::create([
			"content" => (!$q) ? "" : $q->content,
			"user_id" => $this->user->id,
			"stage" => $this->user->stage,
			"key" => Messages::KEY_TYPE_QUESTION
		]));
	}

	/**
	 * Get the message by ID (could be question or answer)
	 *
	 * @return 	int 	$question_id
	 * @since 	1.0.0
	 */
	public function show($question_id)
	{

		return Messages::find($question_id) ?? $this->error("No messages found!");
	}

	/**
	 * Submit answer to question and update users progress (stage)
	 *
	 * @param 	StoreMessageRequest 	$request 	
	 * @return 	JSON 	$return
	 * @since 	1.0.0
	 */
	public function store(StoreMessageRequest $request)
	{

		$return = $this->error("Could not submit answer");
		$data = $request->json()->all();

		// Respond to users message
		$return = $this->doRespond($data);

		// Save message for existing users - NOTE: commented this out, might delete in future...
		// if (!$this->is_guest && !empty((array)$this->user)) {
		// 	$k = $data['key'] ?? false;
		// 	$c = $data['content'] ?? false;
		// 	$u = (int)$data['user_id'] ?? false;
		// 	$p = (int)$data['page_id'] ?? false;
		// 	$s = (int)$data['stage'] ?? 0;
		// 	$n = $data['name'] ?? '';
		// 	$t = $data['title'] ?? $n;
		// 	$lId = $data['id_linked_content_meta'] ?? 0;
		// 	$message = new Messages;
		// 	$message->name = $n;
		// 	$message->id_linked_content_meta = $lId; // This is how portchris can respond
		// 	$message->title = $t;
		// 	$message->key = $k;
		// 	$message->stage = $s;
		// 	$message->content = $c;
		// 	$message->user_id = $u;
		// 	$message->page_id = $p;
		// 	if (!$message->save()) {
		// 		$sanitizedData['content'] = __('Error: could not save message'); 
		// 		return response()->json(Messages::create([
		// 			'name' => $n,
		// 			'id_linked_content_meta' => $lId, // This is how portchris can respond
		// 			'title' => $t,
		// 			'key' => $k,
		// 			'stage' => $s,
		// 			'content'=> $c,
		// 			'user_id' => $u,
		// 			'page_id' => $p
		// 		]), 500);
		// 	}
		// }

		return $return;
	}

	/**
	 * The user has submitted
	 *
	 * @since 	1.0.0
	 */
	protected function doRespond($data)
	{

		$return = [];
		extract($data);
		$response = Messages::respond($id_linked_content_meta, $content);
		if (!empty((array) $response)) {
			$content = "";
			$a = $response->getAnswer();
			if (!$a) {
				$return = $this->error("Error: the answer is unknown.");
			} else {

				// Convert ChoiceScript variables 
				$content = Messages::convertChoiceScriptVariables($a->content, $this->user);

				// Update the users progress
				User::where("id", $user["id"])->update(["stage" => $a->id]);

				// Set the response JSON
				$return = response()->json(Messages::create([
					'id' => $a->id,
					'content' => $content,
					'question' => $response->getQuestion(),
					'message' => $response->getMessage(),
					'response' => $response->getResponse(),
					'stage' => $a->stage,
					'name' => $a->name,
					'goto' => $a->goto,
					'id_linked_content_meta' => $a->id_linked_content_meta,
					'title' => $a->title,
					'key' => Messages::KEY_TYPE_QUESTION,
					'user_id' => $user["id"],
					'page_id' => $a->page_id
				]), 200);
			}
		} else {
			$return = response()->json(Messages::create([
				'content' => __("No response, please try again later"),
				'stage' => $stage,
				'name' => "Error",
				'id_linked_content_meta' => $id_linked_content_meta,
				'title' => $title,
				'key' => Messages::KEY_TYPE_ERROR,
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
	protected function stage()
	{ }

	/**
	 * Set users progress
	 *
	 * @return 	int $stage
	 * @since 	1.0.0
	 */
	protected function setStage()
	{ }

	/**
	 * Attempt to try and retreive the current user from session or database 
	 *
	 * @return 	object 	$user
	 * @since 	1.0.0
	 */
	private function getUser()
	{

		return $this->user;
	}

	/**
	 * Set a new user object for guests if request has user data
	 *
	 * @param 	array 	$data
	 */
	private function setUser(array $data)
	{

		$this->user = new User;
		$this->user->id = $data['id'] ?? 0;
		$this->user->name = $data['name'] ?? __("Guest");
		$this->user->firstname = $data['firstname'] ?? __("Guest");
		$this->user->lastname = $data['lastname'] ?? __("");
		$this->user->email = $data['email'] ?? "";
		$this->user->username = $data['username'] ?? $this->user->email;
		$this->user->stage = $data['stage'] ?? 1;
	}

	/**
	 * Create JSON error response 
	 *
	 * @return 	JSON object 	$response
	 * @since 	1.0.0
	 */
	public function error($msg)
	{

		return response()->json(Messages::create([
			'content' => __($msg),
			'key' => Messages::KEY_TYPE_ERROR,
			'name' => Messages::RESPONSE_ERROR,
			'title' => Messages::RESPONSE_ERROR,
			'stage' => 0,
			0
		]), 500);
	}

	/**
	 * If the user typed 'Continue as guest' then a key should be present, else they are not verified
	 * NOTE: session is not working so frontend uses HTML local storage instead
	 *
	 * @return 	boolean
	 */
	private function isVerifiedGuest()
	{

		$t = (string) $this->token ?? (string) $this->request->input('token');
		// $k = (string)session('key');
		// $this->is_guest = (strlen($t) > 0 && strlen($k) > 0 && $t === $k) ? true : false;
		$this->is_guest = (strlen($t) > 0) ? true : false;
		return $this->is_guest;
	}
}
