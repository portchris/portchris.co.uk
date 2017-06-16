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
	public function show($question_id) {
		
		return Messages::find($question_id) ?? $this->error("No messages found!");
	}

	/**
	* Submit answer to question and update users progress (stage)
	*
	* @param 	StoreMessageRequest 	$request 	
	* @return 	JSON 	$return
	* @since 	1.0.0
	*/
	public function store(StoreMessageRequest $request) {

		$return = $this->error("Could not submit answer");
		$data = (array)$request->all();
		$k = $data['key'] ?? false;
		$c = $data['content'] ?? false;
		$u = (int)$data['user_id'] ?? false;
		$p = (int)$data['page_id'] ?? false;
		$s = (int)$data['stage'] ?? 0;
		$n = $data['name'] ?? '';
		$t = $data['title'] ?? $n;
		$lId = $data['id_linked_content_meta'] ?? 0;

		// Respond to users message
		$return = $this->doRespond($data);

		// Save message for existing users
		if (!$this->is_guest && !empty((array)$this->user)) {
			$message = new Messages;
			$message->name = $n;
			$message->id_linked_content_meta = $lId; // This is how portchris can respond
			$message->title = $t;
			$message->key = $k;
			$message->stage = $s;
			$message->content = $c;
			$message->user_id = $u;
			$message->page_id = $p;

			// Update users stage

			// if (!$message->save()) {
			// 	$sanitizedData['content'] = __('Error: could not save message'); 
			// 	return response()->json(Messages::create([
			// 		'name' => $n,
			// 		'id_linked_content_meta' => $lId, // This is how portchris can respond
			// 		'title' => $t,
			// 		'key' => $k,
			// 		'stage' => $s,
			// 		'content'=> $c,
			// 		'user_id' => $u,
			// 		'page_id' => $p
			// 	]), 500);
			// }
		}

		return $return;
	}

	/**
	* The user has submitted
	*
	* @since 	1.0.0
	*/
	protected function doRespond($data) {

		$return = [];
		extract($data);
		$response = Messages::respond($id_linked_content_meta, $content);
		if (!empty((array)$response)) {
			$content = "";
			$a = $response->getAnswer();
			if (is_array($a)) {

				// The users input wasn't an exact match we need to inform them
				$answers = "";
				$c = count($a);
				for ($i = 0; $i < $c; $i++) {
					$an = $a[$i];
					if (is_object($an)) {
						$answers .= '"' . $this->convertChoiceScriptTemplate($an->content) . '"';
						$answers .= ($i === ($c - 1)) ? "" : ", ";
					}
				}
				$content = sprintf(__("Sorry, I can't find the appropriate response to your message. The available answers to this question are %s"), $answers);
			} else {
				$content = $this->convertChoiceScriptTemplate($a->content);
			}
			$return = response()->json(Messages::create([
				'id' => $response->getAnswer()->id,
				'content' => $content,
				'question' => $response->getQuestion(),
				'message' => $response->getMessage(),
				'response' => $response->getResponse(),
				'stage' => (int)$stage + 1,
				'name' => $name,
				'id_linked_content_meta' => $id_linked_content_meta,
				'title' => __("Response to: ") . $response->getMessage(),
				'key' => Messages::KEY_TYPE_ANSWER,
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
				'key' => Messages::KEY_TYPE_ANSWER,
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
			'key' => Messages::KEY_TYPE_ERROR,
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

	/**
	* To import my story I have chosen the popular ChoiceScript templating format which
	* uses ${} for their variables. This will convert that to useful user info.   
	*
	* @param 	string 	$str
	* @return 	string 	$str
	*/
	public static function convertChoiceScriptTemplate(string $str) {
		
		$re = '/\${(.*?)\}/';
		$res = (array)$this->user;
		$res['finish'] = Messages::getFinalMessage();
		do {
			preg_match($re, $str, $m);
			if ($m) {
				$info = (isset($res[$m[0][1]])) ? $res[$m[0][1]] : "";
				$str = str_replace($m[0][0], $info, $str);
			}
		} while ($m);
		return $str;
	}
}
