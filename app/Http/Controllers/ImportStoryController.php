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
					count($this->messages["responses"])
				);

				// Save questions
				$saveQ = $this->linkAndSaveMessages(Messages::KEY_TYPE_QUESTION);
				if (!$saveQ) {
					$return .= " " . $this->output("ERROR: Could not save questions to database.", true);
				}

				// Save responses
				$saveR = $this->linkAndSaveMessages(Messages::KEY_TYPE_RESPONSE);
				if (!$saveR) {
					$return .= " " . $this->output("ERROR: Could not save responses to database.", true);
				}
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
	* Save all questions first to create an ID then tie the responses to each question of the same stage
	*
	* @param 	string 	$type 	Message key type
	* @return 	boolean 
	*/
	private function linkAndSaveMessages(string $type) {

		$r = false;
		$typeId = strtoupper(substr($type, 0, 1));
		$opp = ($type === Messages::KEY_TYPE_RESPONSE) ? Messages::KEY_TYPE_QUESTION : Messages::KEY_TYPE_RESPONSE;
		$linkedTypeId = strtoupper(substr($opp, 0, 1));
		$linkedKey = $opp . "s";
		$messages = &$this->messages[$type . "s"];
		foreach ($messages as &$msg) {
			$stage = $msg["stage"];
			$lId = $this->findLinkedMessage($msg, $linkedKey, $linkedTypeId);
			if ($lId === false) {
				$r = false;
				break;
			}
			$msg["name"] = $this->output("Next %s on from ID: %d", $type, $lId);
			$msg["id_linked_content_meta"] = $lId;
			if ((int)$msg["id"] === 0) {
				$msg = $this->saveMessage($msg);
				if (!$msg) {
					$r = false;
					break;
				} else {
					$r = true;
				}
			} else {
				$m = Messages::find((int)$msg["id"]);
				$m->id_linked_content_meta = $lId;
				if (!$m->save()) {
					$r = false;
					break; 
				} else {
					$r = true;
				}
			}
		}
		return $r;
	}

	/**
	* Identify the previous question this
	*
	* @param 	array 	$msg
	* @param 	string 	$key
	* @param 	string 	$typeId
	* @return 	int 	
	*/
	private function findLinkedMessage(array $msg, string $key, string $typeId) {

		$r = 0;
		$messages = &$this->messages[$key];
		$position = preg_replace("/[^0-9]/", "", $msg["title"]);
		$id = $this->calcLinkedStage($msg["title"], $typeId);

		// The first possible position is 11 which cannot be linked
		if ($position !== "11") {
			foreach ($messages as &$q) {
				if ($q['title'] === $id) {
					if ((int)$q["id"] === 0) {

						// We found a link but it is not yet available in the db
						$this->output("%s looking for %s. CONTENT = %s", $msg["title"], $id, $msg["content"], true);

						// $q["id_linked_content_meta"] = $msg["id"];
						// $q["name"] = $this->output("Next %s on from %sID: %d", $key, $typeId, $msg["id"]);
						$q = $this->saveMessage($q);
						if (!$q) { 
							$r = false;
							break;
						} else {
							$r = (int)$q["id"];
						} 
					} else {

						// We found a link available in the db
						$this->output("%s looking for %s. CONTENT = %s", $msg["title"], $id, $msg["content"], true);
						$r = (int)$q["id"];
					}
					break;
				}
			}
		} else if ($typeId === "Q") {
			$r = $messages[0]["id"];
		}
		return $r;
	}

	/**
	* Questions and responses always link to the message of the previous stage.
	* This is the logic to calculate it.
	*
	* @param 	string 	$title 	Current message title
	* @param 	string 	$typeId 	Response or Question
	* @param 	string 	The title to search for
	*/
	private function calcLinkedStage($title, $typeId) {

		$position = preg_replace("/[^0-9]/", "", $title);
		$msgNo = intval(substr($position, 0, 1)); 
		$stage = intval(substr($position, 1));
		if ($typeId === "Q") {
			$noOfR = $this->findResponsesAtStage($stage);
			$this->output("Num of responses %s", $noOfR, true);
		}
		return $typeId . $msgNo . " Stage " . ($stage - 1);
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

	private function findResponsesAtStage($stage) {

		$responses = 0;
		$messages = $this->getMessages();
		foreach ($messages["responses"] as $r) {
			if (strpos($r["title"], "Stage " . $stage) !== false) {
				$responses++;
			}
		}
		return $responses;
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
			$stage = (substr_count($newline, "\t") + 1) - $choices;
			// $stage = ($stage <= 0) ? 1 : $stage;
			$delimiter = substr(str_replace(["\t"], "", $newline), 0, 1);
			switch ($delimiter) {
				case self::DELIMITER_ACTION:
					$actions = explode(" ", $newline);
					$action = str_replace([PHP_EOL, "\t", $delimiter], "", $actions[0]);
					switch ($action) {
						case self::ACTION_FINISH:
							$nextStage = (isset($explosion[$i+1])) ? (substr_count($explosion[$i+1], "\t") + 1) : 0;
							$currentStage = substr_count($newline, "\t");
							if ($nextStage < $currentStage) { 
								$choices = $nextStage;
								$stagedResponses = [];
							}
						break;
						case self::ACTION_CHOICE:
							// $choices = substr_count($newline, "\t");
							$choices++;
							$this->vardump($choices);
						break;
					}
				break;
				case self::DELIMITER_OPTION:

					// This is a response
					// $stage--;
					$stagedResponses["S" . $stage] = (isset($stagedResponses["S" . $stage])) ? $stagedResponses["S" . $stage] += 1 : 1;
					$pos = $this->output("R%d Stage %d", $stagedResponses["S" . $stage], $stage);
					$m = Messages::create([
						"content" => substr(str_replace([PHP_EOL, "\t"], "", $newline), 1),
						"key" => Messages::KEY_TYPE_RESPONSE,
						"page_id" => $this->getPageId(),
						"user_id" => $this->getUserId(),
						"stage" => $stage,
						"title" => $pos
					]);
					$return = array_merge($return, $m);
				break;
				default:

					// This is a question
					$stagedQuestions["S" . $stage] = (isset($stagedQuestions["S" . $stage])) ? $stagedQuestions["S" . $stage] += 1 : 1;
					$pos = $this->output("Q%d Stage %d", $stagedQuestions["S" . $stage], $stage);
					$m = Messages::create([
						"content" => str_replace([PHP_EOL, "\t"], "", $newline),
						"key" => Messages::KEY_TYPE_QUESTION,
						"page_id" => $this->getPageId(),
						"user_id" => $this->getUserId(),
						"stage" => $stage,
						"title" => $pos,
					]);
					// if (isset($explosion[$i+1])) {
					// 	$nextR = (isset($stagedResponses["S" . ($stage + 1)])) ? $stagedResponses["S" . ($stage + 1)] + 1 : 1;
					// 	$rNo = $this->findResponsesAtStage($stage);
					// 	$m[0]["lId"] = $this->output("R%d Stage %d", $nextR, $stage);
					// }
					$return = array_merge($return, $m);
				break;
			}
		}
		// $this->vardump($return); die;
		// for ($i = 0; $i < $c; $i++) {
		// 	$action = "";
		// 	$messages = [];
		// 	$x = $i;
		// 	$choices = 0;
		// 	$this->vardump(str_replace(["\r", "\n"], "", $explosion[$x]), $stagedQuestions, $stagedResponses);
		// 	do {
		// 		$newline = str_replace(["\r", "\n"], "", $explosion[$x]);
		// 		$stage = (substr_count($newline, "\t") + 1) - $choices;
		// 		$delimiter = substr(str_replace(["\t"], "", $newline), 0, 1);
		// 		switch ($delimiter) {
		// 			case self::DELIMITER_ACTION:

		// 				// This is an action
		// 				$actions = explode(" ", $newline);
		// 				$action = str_replace([PHP_EOL, "\t", $delimiter], "", $actions[0]);
		// 				switch ($action) {
		// 					case self::ACTION_FINISH:
		// 						$return = array_merge($return, $messages);
		// 						$i = $x;
		// 						continue 2;
		// 					break;
		// 					case self::ACTION_CHOICE:
		// 						$x++;
		// 						$choices++;
		// 						continue;
		// 					break;
		// 				}
		// 			break;
		// 			case self::DELIMITER_OPTION:

		// 				// This is a new response
		// 				$stagedResponses["S" . $stage] = (isset($stagedResponses["S" . $stage])) ? $stagedResponses["S" . $stage] += 1 : 1;
		// 				$pos = $this->output("R%d Stage %d", $stagedResponses["S" . $stage], $stage);
		// 				$m = Messages::create([
		// 					"content" => substr(str_replace([PHP_EOL, "\t"], "", $newline), 1),
		// 					"key" => Messages::KEY_TYPE_RESPONSE,
		// 					"page_id" => $this->getPageId(),
		// 					"user_id" => $this->getUserId(),
		// 					"stage" => $stage,
		// 					"title" => $pos,
		// 				]);
		// 				$messages = array_merge($messages, $m);
		// 				$x++;
		// 				continue;
		// 			break;
		// 			default:

		// 				// This is a new question
		// 				$stagedQuestions["S" . $stage] = (isset($stagedQuestions["S" . $stage])) ? $stagedQuestions["S" . $stage] += 1 : 1;
		// 				$pos = $this->output("Q%d Stage %d", $stagedQuestions["S" . $stage], $stage);
		// 				$m = Messages::create([
		// 					"content" => str_replace([PHP_EOL, "\t"], "", $newline),
		// 					"key" => Messages::KEY_TYPE_QUESTION,
		// 					"page_id" => $this->getPageId(),
		// 					"user_id" => $this->getUserId(),
		// 					"stage" => $stage,
		// 					"title" => $pos,
		// 				]);
		// 				$messages = array_merge($messages, $m);
		// 				$x++;
		// 				continue;
		// 			break;
		// 		}
		// 	} while ($action !== self::ACTION_FINISH);
		// }
		// $this->vardump($return);
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
	*/
	private function vardump() {

		$args = func_get_args();
		foreach ($args as $str) {
			echo "<pre>"; var_dump($str); echo "</pre>";
		}
	}
}
