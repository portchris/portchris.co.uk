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
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessageRequest;

class MessageStreamController extends Controller
{
	const RESPONSE_ERROR = 'error';

	/**
	* Get current user, progress and current question
	*
	* @since 	1.0.0
	* @return 	array 	User | Question | Stage
	*/
	public function index() {

		return Messages::all();
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
		$u = $data['user_id'] ?? false;
		$p = $data['page_id'] ?? false;
		$s = $data['stage'] ?? false;
		$n = $data['name'] ?? '';
		$t = $data['title'] ?? $n;
		$lId = $data['id_linked_content_meta'] ?? '';

		if ($k && $c && $u && $p && $s) {
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
			if ($message->save()) {
				$response = Messages::respond($lId, $c);
				if (!empty((array)$response)) {
					$return = response()->json([
						'answer' => $response->getAnswer(),
						'question' => $response->getQuestion(),
						'message' => $response->getMessage(),
						'response' => $response->getResponse()
					]);
				}
			}
		}
		return $return;
	}

	/**
	* The user has submitted
	*
	* @since 	1.0.0
	*/
	protected function getResponse() {


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

		return response()->json(["name" => self::RESPONSE_ERROR, "content" => __($msg)]);
	}
}
