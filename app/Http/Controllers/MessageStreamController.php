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
	public function store($data) {

		echo "HERE: " . __FUNCTION__;
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
	private function error($msg) {

		return response()->json(["name" => self::RESPONSE_ERROR, "content" => __($msg)]);
	}
}
