<?php
/**
* Content Metas are specific to pages, they are content blocks. There is added functionality that 
* allows content metas to be used as questions and answers thus creating our text based adventure.
*
* @author 	Chris Rogers
* @since 	1.0.0 (2017-04-27)
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
// use \DB;

class ContentMeta extends Model
{
	/**
	* The users inputted response
	* @var 	string
	*/
	private $message;

	/**
	* The closest response from users message
	* @var 	object
	*/
	private $response;

	/**
	* The answer to the users inputted message
	* @var 	object|array 	could be array of answers (objects) if no suitable response found
	*/
	private $answer;

	/**
	* The next available question
	* @var 	object
	*/
	private $question;

	/**
	* How strict the response match;
	* @var 	int
	*/
	private $accuracy;

	/**
	* Prefab message template
	* @var 	array
	*/
	public const MESSAGE_TEMPLATE = [	
		'id' => 0,
		'user_id' => 0,
		'page_id' => 0,
		'id_linked_content_meta' => 0,
		'name' => "",
		'title' => "",
		'key' => "",
		'stage' => "",
		'content' => "",
		'type' => "",
		'method' => "",
		'created_at' => "",
		'updated_at' => ""
	];

	/**
	* Prefab message types
	* @var 	array
	*/
	public const TYPES = [	
		'ContentMeta' => "message",
		'Page' => "page",
		'Role' => "role",
		'User' => "user"
	];

	
	/**
	* The closest response from users message
	* @var 	object
	*/
	protected $fillable = [
		'id_linked_content_meta', 'name', 'title', 'key', 'content', 'stage', 'user_id', 'page_id'
	];

	/**
	* ContentMetas belong to pages. This is a one-to-many (inverse) relationship. 
	*
	* @return 	object 	Page
	*/
	public function page() {
		
		return $this->belongsTo('App\Page');
	}

	/**
	* ContentMetas also belong to users. Again this is a one-to-many (inverse) relationship. 
	*
	* @return 	object 	User
	*/
	public function user() {

		return $this->belongsTo('App\User');
	}


	/**
	* Create and return new message using template constant
	*
	* @param 	string 	$content
	* @param 	string 	$key
	* @param 	string 	$name
	* @param 	string 	$title
	* @param 	int 		$stage
	* @param 	string 	$type
	* @param 	string 	$method
	* @return 	array 	$msg
	*/
	public static function create($content, $key, $name, $title, $stage, $type = "", $method = "") {

		$Message = new self();
		$type = (strlen($type) === 0) ? self::TYPES[__CLASS__] : $type;
		$method = (method_exists(array_search($type, self::TYPES), $method)) ? $method : "";
		$msg = $Message::MESSAGE_TEMPLATE;
		$msg["content"] = $content;
		$msg["key"] = $key;
		$msg["name"] = $name;
		$msg["title"] = $title;
		$msg["stage"] = $stage;
		$msg["type"] = $type;
		$msg["method"] = $method;
		return array($msg);
	}

	/**
	* The user has submitted a message to a question asked by the portchris engine.
	* We need to get all the linked responses for this question and the corresponding answer. 
	* We will compare the users message with our responses using the metaphone method then taking  
	* the levenshtein distance between these 2 sentences. If a certain level of accuracy is not reached then
	* the portchris engine will list all the possible responses. If after the second response the engine 
	* still cannot find a match, a "feeling lucky" Google search will be made. 
	* Stage 0 responses are stage-less and therefore can be used at any point during the game. 
	*
	* @since 	1.0.0
	* @param 	int 		$question_id 	
	* @param 	string 	$users_response
	* @param 	int 		$accuracy
	*/
	public static function respond($question_id, $users_response, $accuracy = 50) {

		$Message = new self();
		$Message->setAccuracy($accuracy);
		$Message->setMessage($users_response);
		$Message->talk($question_id);
		return $Message;
	}

	/**
	* The main bulk logic for the portchris engine text-based adventure
	*
	* @since 	1.0.0
	* @param 	int 		$question_id 	
	*/
	public function talk($question_id) {
		
		// Get all possible responses to linked question from db
		// $this->message = $users_response;
		// var_dump($possible_responses->toSql(), $possible_responses->getBindings()); die;
		$staged_responses = self::where([
			'id_linked_content_meta' => $question_id,
			'key' => 'response'
		])->get();
		$stageless_responses = self::where([
			'stage' => '0',
			'key' => 'response'
		])->get();
		$possible_responses = $staged_responses->merge($stageless_responses);
		if (!$possible_responses->isEmpty()) {

			// Find the closet matched response from above
			$closest_response = $this->getClosestResponse($possible_responses);
			$this->setResponse($closest_response);
			
			// Calculate the validity of our closest response against the set accuracy required to pass
			$percent = $this->calculateResponseMatch($closest_response->content);
			if ($percent >= $this->getAccuracy()) {
				
				// This seems an appropriate response. Get answer to closest linked response
				$a = self::where([
					'id_linked_content_meta' => $closest_response->id,
					'key' => 'answer'
				])->first();
				$this->setAnswer($a);
			} else {

				// Response not accurate enough, explain all possible responses available
				$this->setAnswer($staged_responses);
			}
		} else {

			// No responses... let the user know there has been an error.
		}
	}

	/**
	* Calculate what the user has said and find the closet matched response.
	*
	* @param 	array 	$possible_responses.
	* @return 	obj 		
	*/
	private function getClosestResponse($possible_responses) {

		$shortest = -1; // No shortest distance found, yet
		$closest = "";
		$ur_phoneme = metaphone($this->getMessage());
		foreach ($possible_responses as $response) {
			$r_phoneme = metaphone($response->content);
			$l = levenshtein($ur_phoneme, $r_phoneme);
			
			// Check for exact match
			if ($l == 0) { 

				// Closest word is this one (exact match)
				$closest = $response;
				$shortest = 0;
				break;
			}

			// If distance is less than the next shortest distance OR if a next shortest is not yet found
			if ($l <= $shortest || $shortest < 0) {
				
				// Set the closest match, and shortest distance
				$closest  = $response;
				$shortest = $l;
			}
		}

		return $closest;
	}

	/**
	* Check how close the strings match and return a percentage using the levenshtein algorithm 
	*
	* @param 	string 	$response1 
	* @return 	int 		percentage
	*/
	private function calculateResponseMatch($response1) {

		$response2 = $this->getMessage();
		$accuracy = 1 - levenshtein($response1, $response2) / max(strlen($response1), strlen($response2));
		return ceil($accuracy * 100);
	}

	/**
	* Set the users message. 
	*
	* @since 	1.0.0
	* @param 	string 	$msg
	*/
	private function setMessage($msg) {

		$this->message = $msg;
	}

	/**
	* Get the users message. 
	*
	* @since 	1.0.0
	* @return 	string
	*/
	public function getMessage() {

		return $this->message;
	}

	/**
	* Set the closest matched response from users message. 
	*
	* @since 	1.0.0
	* @param 	object 	$r
	*/
	private function setResponse($r) {

		$this->response = $r;
	}

	/**
	* Get the closest matched response from users message. 
	*
	* @since 	1.0.0
	* @return 	object
	*/
	public function getResponse() {

		return $this->response;
	}

	/**
	* Set the answer to the users message based on response. 
	*
	* @since 	1.0.0
	* @param 	object 	$q
	*/
	private function setAnswer($a) {

		$this->answer = $a;
	}

	/**
	* Get the answer to the users message based on response. 
	*
	* @since 	1.0.0
	* @return 	object
	*/
	public function getAnswer() {

		return $this->answer;
	}

	/**
	* Set the next question of the portchris engine. 
	*
	* @since 	1.0.0
	* @param 	object 	$q
	*/
	private function setQuestion($q) {

		$this->question = $q;
	}

	/**
	* Get the next question of the portchris engine. 
	*
	* @since 	1.0.0
	* @return 	object
	*/
	public function getQuestion() {

		return $this->question;
	}

	/**
	* Set how strict the response matching should be.
	*
	* @param 	int 	$acc;	
	*/
	private function setAccuracy($acc) {

		$this->accuracy = $acc;
	}

	/**
	* Get how strict the response matching should be.
	*
	* @return 	int
	*/
	private function getAccuracy() {

		return $this->accuracy;
	}

	/**
	* Filter all the responses and return only the staged ones. Part of PHP function "array_filter"
	*
	* @param 	$v
	* @return 	All values without stage of 0
	*/
	// private function getPossibleResponses($v) {

	// 	return ($v->stage != 0);
	// }
}
