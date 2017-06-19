<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\ContentMeta as Messages;
use App\Page;
use App\User;

class ImportStoryController extends Controller
{
	/**
	* Absolute app/storage location
	* @var 	string
	*/
	private $importPath;	

	/**
	* The page these messages belong to
	* @var 	int
	*/
	private $pageId;
	
	/**
	* The author of these messages (me)
	* @var 	int
	*/
	private $userId;

	/**
	* All the messages found in the file, follows the MESSAGES_TEMPLATE
	* @var 	array
	*/
	private $messages;

	/**
	* These are private settings that conform to the choicescript format
	* @var 	const 	string 	
	*/
	private const STORY_DIR = "story/";
	private const FILE_EXT = ".txt";
	private const DELIMITER_ACTION = "*";
	private const DELIMITER_OPTION = "#";
	private const ACTION_CHOICE = "choice";
	private const ACTION_FINISH =	"finish";

	/**
	* The standardised array format to aid the interpreter
	* @var 	const 	array
	*/
	private const MESSAGES_TEMPLATE = [
		"questions" => [],
		"responses" => []
	];

	public function __construct() {

		$this->importPath = storage_path(self::STORY_DIR);
		$this->setUserId();
	}

	/**
	* Using the ID provided check if file exists, interpret and import the file for easier story writing.
	*
	* @param  Request  $request
	* @param  string  $id
	* @return Response
	*/
	public function __invoke($id) {

		$return = $file = "";
		$slug = ($id == Page::PAGE_HOMEPAGE) ? "/" : $id;
		$id .= self::FILE_EXT;
		$this->setPageId($slug);
		if (File::exists($this->importPath . $id) && $this->getPageId() !== false) {
			$return .= $this->output("Found file of ID: %s", $id, true);
			try {

				// Truncate content_metas table
				Messages::getQuery()->delete();

				// Convert file into readable format for eloquent model
				$this->setMessages($this->interpretFile($id));
				$return .= " " . $this->output(
					"Found %d messages: %d questions, %d responses.", 
					count($this->messages["questions"]) + count($this->messages["responses"]), 
					count($this->messages["questions"]),
					count($this->messages["responses"]),
					true
				);
				$this->vardump($this->getMessages());
			} catch (Exception $e) {
				$return .= " " . $this->output($e->getMessage());
			}
		} else {
			$return .= $this->output("Cannot find file of ID: %s", $id);
		}
		return $return;
	}

	/**
	* The formula to work out what stage this message is
	* 
	* @param 	string 	$line
	* @return 	int 		$stage
	*/
	private function calcStage(string $line) {

		return (int)floor((substr_count($line, "\t") / 2) + 1);
	}

	/**
	* Save the message to the db
	* 
	* @param 	array 	$msg 		MUST follow the ContentMeta eleoquent model
	* @return 	boolean|array 	$r
	*/
	private function saveMessage($msg) {

		$r = false;
		$Message = new Messages;
		$Message->title = $msg["title"];
		$Message->stage = $msg["stage"];
		$Message->id_linked_content_meta = $msg["id_linked_content_meta"];
		$Message->name = $msg["name"];
		$Message->content = $msg["content"];
		$Message->key = $msg["key"];
		$Message->page_id = $this->getPageId();
		$Message->user_id = $this->getUserId();
		if (!$Message->save()) { 
			$r = false; 
		} else {
			$Message = Messages::findOrFail($Message->id);
			$msg["id"] = $Message->id;
			$r = $msg;
		}
		return $r;
	}

	/**
	* Portchris story text files support the choicescript format. This is a custom interpretter in order
	* to store stories as messages in the portchris database.
	*
	* @see 	https://www.choiceofgames.com/make-your-own-games/choicescript-intro/
	* @return 	array 	formatted to the ContentMeta eloquent model
	*/
	private function interpretFile($file) {

		$messages = $this->messagesTemplate();
		$file = File::get($this->importPath . $file);
		$allMessages = $this->convertFileToMessages($file);
		$messages["responses"] = $this->filterMessages($allMessages, "key", Messages::KEY_TYPE_RESPONSE);
		$messages["questions"] = $this->filterMessages($allMessages, "key", Messages::KEY_TYPE_QUESTION);
		return $messages;
	}

	/**
	* Using the file contents, convert the string to an array in the format of the Message eloquent model 
	*
	* @param 	string 	$file
	* @return 	array 	$return
	*/
	private function convertFileToMessages(string $file) {

		$choices = 0;
		$return = [];
		$explosion = explode(PHP_EOL, $file);
		$c = count($explosion);
		$stagedQuestions = [];
		$stagedResponses = [];
		for ($i = 0; $i < $c; $i++) {
			$newline = str_replace(["\r", "\n"], "", $explosion[$i]);
			$stage = $this->calcStage($newline);
			$delimiter = substr(str_replace(["\t"], "", $newline), 0, 1);
			switch ($delimiter) {
				case self::DELIMITER_ACTION:
					$actions = explode(" ", $newline);
					$action = str_replace([PHP_EOL, "\t", $delimiter], "", $actions[0]);
					switch ($action) {
						case self::ACTION_FINISH:
							// $nextStage = (isset($explosion[$i+1])) ? (substr_count($explosion[$i+1], "\t") + 1) : 0;
							// $currentStage = substr_count($newline, "\t");
							// if ($nextStage < $currentStage) { 
							// 	$choices = $nextStage;
							// 	// $stagedResponses["S" . $stage] = 0;
							// }

							// Append this as a variable on the end of the last question
							// for ($x = $i; $x > 0; $x--) {
							// 	$lastMsg = &$explosion[$x];
							// 	$delimiter = substr(str_replace(["\t"], "", $lastMsg), 0, 1);
							// 	if ($delimiter !== self::ACTION_CHOICE && $delimiter !== self::ACTION_FINISH) {
							// 		$lastMsg["content"] .= " " . substr(str_replace([PHP_EOL, "\t"], "", $newline), 1);	
							// 		break;
							// 	}
							// }
						break;
						case self::ACTION_CHOICE:
							$choices = substr_count($newline, "\t");
						break;
					}
				break;
				case self::DELIMITER_OPTION:

					// This is a response
					$stagedResponses["S" . $stage] = (isset($stagedResponses["S" . $stage])) ? $stagedResponses["S" . $stage] += 1 : 1;

					// Traverse backward through the array until we find the nearest question that matches our stage
					$title = "";
					$lId = 0;
					$x = $i - 1;
					while (strlen($title) === 0) {
						$lastMsg = $explosion[$x];
						$delimiter = substr(str_replace(["\t"], "", $lastMsg), 0, 1);
						if ($delimiter !== self::ACTION_CHOICE && $delimiter !== self::ACTION_FINISH) {
							$qs = $this->calcStage($lastMsg);
							if ($qs === ($stage)) {
								foreach ($stagedQuestions["S" . $stage] as $q) {
									if ($q["pos"] === $x) {
										$title = $q["t"];
										$lId = $q["id"];
										break;
									}
								}
							}
						}
						$x--;
					}
					$m = $this->saveMessage([
						"content" => substr(str_replace([PHP_EOL, "\t"], "", $newline), 1),
						"key" => Messages::KEY_TYPE_RESPONSE,
						"page_id" => $this->getPageId(),
						"user_id" => $this->getUserId(),
						"stage" => $stage,
						"title" => $title,
						"name" => $this->output("Available response to question: %d", $lId),
						"id_linked_content_meta" => $lId
					]);
					if (!$m) {
						throw new Exception("Error saving response to database.");
					} else {
						$return[] = $m;
					}
				break;
				default:

					// This is a question
					$lId = ($stage > 1 && isset($return[(count($return) - 1)])) ? $return[(count($return) - 1)]["id"] : 0;
					$stagedQuestions["S" . $stage] = (!isset($stagedQuestions["S" . $stage])) ? [] : $stagedQuestions["S" . $stage];
					$title = $this->output("Q%d Stage %d", count($stagedQuestions["S" . $stage]) + 1, $stage);
					$m = $this->saveMessage([
						"content" => str_replace([PHP_EOL, "\t"], "", $newline),
						"key" => Messages::KEY_TYPE_QUESTION,
						"page_id" => $this->getPageId(),
						"user_id" => $this->getUserId(),
						"stage" => $stage,
						"title" => $title,
						"name" => $this->output("Next question on from response: %d", $lId),
						"id_linked_content_meta" => $lId
					]); 
					if (!$m) {
						throw new Exception("Error saving question to database");
					} else {
						$stagedQuestions["S" . $stage][] = ["pos" => $i, "t" => $title, "id" => $m["id"]];
						$return[] = $m;
					}
				break;
			}
		}
		return $return;
	}

	/**
	* Separate messages by filter
	* 
	* @param 	array 	$messages 
	* @param 	string 	$filterKey
	* @param 	string 	$filterValue
	* @param 	array 	$return
	*/
	private function filterMessages($messages, $filterKey, $filterValue) {

		$return = [];
		foreach ($messages as $msg) {
			if ($msg[$filterKey] === $filterValue) {
				$return[] = $msg;
			}
		}
		return $return;
	}

	/**
	* Return the standardised format for creating staged (multi-dimentional) messages
	*
	* @return 	array from const
	*/
	private function messagesTemplate() {

		return self::MESSAGES_TEMPLATE;
	}

	/**
	* Set the messages array
	*
	* @param 	array 	$messages
	*/
	private function setMessages(array $messages) {

		$this->messages = $messages;
	}

	/**
	* Get the messages array
	*
	* @return 	array 	$messages
	*/
	private function getMessages() {

		return $this->messages;
	}

	/**
	* Judging by the name of the file we can tell what page these messages are for.
	*
	* @param 	string 	$slug
	*/
	private function setPageId($slug) {

		$page = Page::where("slug", "=", $slug)->first();
		$this->pageId = (!$page) ? false : $page->id;
	}

	/**
	* Quite simply, this is an administrative controller, so the only admin is me
	*/
	private function setUserId() {

		$user = User::where("email", "=", User::ADMIN_EMAIL)->first();
		$this->userId = (!$user) ? 0 : $user->id;
	}

	/**
	* Get the page ID.
	*
	* @return 	string 	$pageId
	*/
	private function getPageId() {

		return $this->pageId;
	}

	/**
	* Get the user ID.
	*
	* @return 	string 	$pageId
	*/
	private function getUserId() {

		return $this->userId;
	}

	/**
	* Localised string with support of HTML & placeholders: %s = string %d = int etc
	*
	* @param 	works like vsprintf: first arg has to be string.
	* @param 	boolean 	$echo 	Last argument has to be boolean
	* @return 	string 	$str
	*/
	private function output() {

		$str = "";
		$args = func_get_args();
		$echo = false;
		if (!empty($args)) {
			$last = count($args) - 1;
			$str = __((string)$args[0]);
			if (is_bool($args[$last])) { 
				$echo = $args[$last];
				unset($args[$last]);
			}
			unset($args[0]);
			$str = vsprintf($str, $args);
			if ($echo === true) {
				echo "<p>" . $str . PHP_EOL . "</p>";
			}
		}
		return $str;
	}

	/**
	*	Quick pretty print var dump
	*
	* @param 	array 	$args
	*/
	private function vardump() {

		$args = func_get_args();
		foreach ($args as $str) {
			echo "<pre>"; var_dump($str); echo "</pre>";
		}
	}
}
