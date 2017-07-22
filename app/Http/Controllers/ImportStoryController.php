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
	* Request object
	* @var 	Request 	$request
	*/
	private $request;

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
	* The page slug, used for label and scene
	* @var 	int
	*/
	private $pageSlug;
	
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
	* Scenes / Chapters in the story
	* @var 	array 
	*/
	private $scenes;

	/**
	* The current scenes / chapter of the story
	* @var 	string 
	*/
	private $currentScene;

	/**
	* Key valued list of labels and the ID it's linked to.
	* @var 	array 
	*/
	private $labels;

	/**
	* Key valued list of gotos which link to labels so the user can hop around checkpoints.
	* @var 	array
	*/
	private $gotos;

	/**
	* List of ordered staged questions 
	* @var 	array
	*/
	private $stagedQuestions;

	/**
	* List of unordered stage-less questions 
	* @var 	array
	*/
	private $stagelessQuestions;

	/**
	* List of ordered staged responses to questions
	* @var 	array
	*/
	private $stagedResponses;

	/**
	* List of unordered stage-less responses to questions
	* @var 	array
	*/
	private $stagelessResponses;

	/**
	* These are private settings that conform to the choicescript format
	* @var 	const 	string 	
	*/
	private const START_UP = "startup";
	private const STORY_DIR = "story/";
	private const FILE_EXT = ".txt";
	private const DELIMITER_ACTION = "*";
	private const DELIMITER_OPTION = "#";
	private const DELIMITER_GOTO = ">>";
	private const ACTION_CHOICE = "choice";
	private const ACTION_FINISH =	"finish";
	private const ACTION_LABEL = "label";
	private const ACTION_GOTO = "goto";
	private const ACTION_SCENE_LIST = "scene_list";

	/**
	* The standardised array format to aid the interpreter
	* @var 	const 	array
	*/
	private const MESSAGES_TEMPLATE = [
		"questions" => [],
		"responses" => []
	];

	public function __construct() {

		$this->request = app('request');
		$this->importPath = storage_path(self::STORY_DIR);
		$this->setPageId("");
		$this->setPageSlug("");
		$this->setUserId();
		$this->setMessages([]);
		$this->setScenes([]);
		$this->setCurrentScene(self::START_UP);
		$this->setLabels([]);
		$this->setGoTos([]);
		$this->setStagedQuestions([]);
		$this->setStagelessQuestions([]);
		$this->setStagedResponses([]);
		$this->setStagelessResponses([]);
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
		$this->setPageSlug($slug);
		$this->setPageId($slug);
		if (File::exists($this->importPath . $id)) {
			$return .= $this->output("Found file of ID: %s.", $id);
			try {

				// Truncate content_metas table
				if (strpos($id, self::START_UP) !== false) {
					$return .= " " . $this->output("Truncating messages table.");
					Messages::getQuery()->delete();
				}

				// Convert file into readable format for eloquent model.
				$this->setMessages($this->interpretFile($id));

				// In order to support checkpoints and message hopping, we need to link labels with gotos.
				$this->LinkGoTosAndLabels();

				// Output to inform user
				$return .= " " . $this->output(
					"Found %d messages: %d questions, %d responses.", 
					count($this->messages["questions"]) + count($this->messages["responses"]), 
					count($this->messages["questions"]),
					count($this->messages["responses"])
				);
				// $this->vardump($this->getMessages());
			} catch (Exception $e) {
				$return .= " " . $this->output($e->getMessage());
			}
		} else {
			$return .= $this->output("Cannot find file: %s%s", $this->importPath, $id);
		}
		return response()->json([$return], 200);
	}

	/**
	* The formula to return the necessary information about the new line of the text file
	* 
	* @param 	string 	$line
	* @return 	int 		$stage
	*/
	private function calcNewline(string $line) {

		return str_replace(["\r", "\n"], "", $line);
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
	* The formula to work out the delimiter if this newline has one
	* 
	* @param 	string 	$line
	* @return 	int 		$stage
	*/
	private function calcDelimiter(string $line) {

		return substr(str_replace(["\t"], "", $line), 0, 1);
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
		$Message->goto = $msg["goto"] ?? 0;
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
	* NOTE: This is the main loop that will iterate over each line in the text file.
	*
	* @param 	string 	$file
	* @return 	array 	$return
	*/
	private function convertFileToMessages(string $file) {

		$return = [];
		$choices = $idLinkedContentMeta = $goto = 0;
		$explosion = explode(PHP_EOL, $file);
		$c = count($explosion);
		$slug = $this->getPageSlug();
		$scenes = $this->getScenes();
		$stagedQuestions = $this->getStagedQuestions();
		$originalScene = $this->getCurrentScene();
		for ($i = 0; $i < $c; $i++) {
			$newline = $this->calcNewline($explosion[$i]);
			$stage = $this->calcStage($newline);
			$delimiter = $this->calcDelimiter($newline);
			if (strlen($newline) > 0) {	
				switch ($delimiter) {
					case self::DELIMITER_ACTION:
						$actions = explode(" ", $newline);
						$action = str_replace([PHP_EOL, "\t", $delimiter], "", $actions[0]);
						switch ($action) {
							case self::ACTION_SCENE_LIST:

								// Keep looping scenes until it ends. Set the iteration and continue.
								$this->setScenes($this->createSceneList($explosion, $i));
								$originalScene = $this->getCurrentScene();
							break;
							case self::ACTION_FINISH:

								// This scene has finished, set the goto to the first question of the next scene
								$lastMsg = $return[count($return) - 1];
								$this->finishScene($lastMsg);
							break;
							case self::ACTION_LABEL:

								// The following newline is a checkpoint for *goto actions to hop to at any time
								$this->addLabel($actions[1], $this->calcNewline($explosion[$i+1]));
							break;
							case self::ACTION_GOTO:

								// Assign the id_linked_content_meta of the previous response to the message ID of the label
								$label = $actions[1];
								// $idLinkedContentMeta = self::DELIMITER_GOTO;
								$lastResponse = $return[count($return) - 1];
								$this->addGoTo($lastResponse["id"], $label);
							break;
							case self::ACTION_CHOICE:
								$choices = substr_count($newline, "\t");
							break;
						} // End switch
					break;
					case self::DELIMITER_OPTION:

						// This is a response
						$return[] = $this->createResponse($explosion, $idLinkedContentMeta, $i);

						// Reset after assignment
						$this->setCurrentScene($originalScene);
						$idLinkedContentMeta = 0; 
					break;
					default:

						// This is a question
						$lId = ($stage > 1 && isset($return[(count($return) - 1)])) ? $return[(count($return) - 1)]["id"] : 0;
						$idLinkedContentMeta = ($idLinkedContentMeta === 0) ? $lId : $idLinkedContentMeta;
						$q = $this->createQuestion($explosion, $idLinkedContentMeta, $i);
						$labelPos = $this->isQuestionLabel($newline);
						if ($labelPos >= 0) {
							$this->assignLabelId($labelPos, $q["id"]);
						}
						$return[] = $q;

						// Reset after assignment
						$this->setCurrentScene($originalScene);
						$idLinkedContentMeta = 0; 
					break;
				} // End switch delimiter
			} // Of of if newline strlen
		} // End for loop
		return $return;
	}

	/**
	* We have reached the end of responses for questions at this stage. Begin next scene.
	* 
	* @param 	array 	$lastMsg
	*/
	private function finishScene($lastMsg) {

		$nextScene = $this->goToNextScene();
		$goto = ($this->getCurrentScene() === $nextScene) ? 0 : $nextScene;
		$msg = Messages::findOrFail($lastMsg["id"]);
		if (!is_null($msg)) {
			$msg->goto = $goto;
			$msg->save();
		}
	}

	/**
	* Current scene has finished, set the id_linked_content_meta to the first question of the next scene.
	* NOTE: This method requires the "main loop"
	*
	* @param 	array 	$explosion 	exploded array of each line in text file
	* @param 	int 	$i 	Iteration in loop
	*/
	private function goToNextScene() {

		$r = $this->getCurrentScene();
		$scenes = $this->getScenes();
		$xc = count($scenes);
		for ($x = 0; $x < $xc; $x++) {
			$scene = $scenes[$x];
			if ($scene == $r) {
				$r = $scenes[$x+1] ?? $r;
				break;
			}
		}
		return $r;
	}

	/**
	* Keep looping scenes until it ends. Set the iteration and continue.
	* NOTE: This method requires the "main loop"
	*
	* @param 	array 	$explosion 	exploded array of each line in text file
	* @param 	int 	$i 	Iteration in main loop
	*/
	private function createSceneList($explosion, &$i) {

		$r = [];
		$c = count($explosion);
		$newline = $this->calcNewline($explosion[$i]);
		$slug = $this->getPageSlug();
		$delimiters = [self::DELIMITER_OPTION, self::DELIMITER_ACTION];
		for ($x = ($i+1); $x < $c; $x++) {
			$nextNewline = str_replace(["\r", "\n"], "", $explosion[$x]);
			$nextStage = $this->calcStage($newline);
			$nextDelimiter = $this->calcDelimiter($nextNewline);
			if ($nextStage === 1 && !in_array($nextDelimiter, $delimiters) && strlen($nextNewline) > 1) {

				// This is another scene to add to the list
				$scene = strtolower(str_replace(["\t"], "", $nextNewline));
				$r = $this->addScene($scene);
				if ($slug == $scene || $slug == Page::PAGE_HOMEPAGE) {
					$this->setCurrentScene($scene);
				}
			} else {

				// Set the parent (main) loop's iterator as we have already checked these lines
				$i = $x;
				break;
			}
		}
		return $r;
	}

	/**
	* Create a response in the messages db
	* NOTE: This method requires the "main loop"
	*
	* @param 	array 	$explosion 	exploded array of each line in text file
	* @param 	string|int 	$idLinkedContentMeta 	linking message in db
	* @param 	int 	$i 	Iteration in main loop
	* @return  	Messages 	$m 	Response 	
	*/
	private function createResponse($explosion, $idLinkedContentMeta, $i) {

		// Traverse backward through the array until we find the nearest question that matches our stage
		$title = "";
		$lId = 0;
		$newline = $this->calcNewline($explosion[$i]);
		$stage = $this->calcStage($newline);
		$delimiter = $this->calcDelimiter($newline);
		$stagedResponse = $this->setMessageAtStage($stage, Messages::KEY_TYPE_RESPONSE);
		$x = $i - 1;
		while (strlen($title) === 0) {
			$lastMsg = $explosion[$x];
			$delimiter = substr(str_replace(["\t"], "", $lastMsg), 0, 1);
			if ($delimiter !== self::ACTION_CHOICE && $delimiter !== self::ACTION_FINISH) {
				$qs = $this->calcStage($lastMsg);
				if ($qs === ($stage)) {
					$stagedQuestion = $this->getMessageAtStage($stage);
					foreach ($stagedQuestion as $q) {
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
			"name" => $this->getCurrentScene(),
			"id_linked_content_meta" => ($idLinkedContentMeta === 0) ? $lId : $idLinkedContentMeta
		]);
		if (!$m) {
			throw new Exception("Error saving response to database.");
		} else {
			$this->setMessageValueAtStage($stage, $i, $title, $m["id"], Messages::KEY_TYPE_RESPONSE);
			return $m;
		}
	}

	/**
	* Create a question in the messages db
	* NOTE: This method requires the "main loop"
	*
	* @param 	array 	$explosion 	exploded array of each line in text file
	* @param 	string | int 	$idLinkedContentMeta 	linking message in db
	* @param 	int 	$i 	Iteration in main loop
	* @return 	Messages 	$m 	Question
	*/
	private function createQuestion($explosion, $idLinkedContentMeta, $i) {

		$newline = $this->calcNewline($explosion[$i]);
		$stage = $this->calcStage($newline);
		$delimiter = $this->calcDelimiter($newline);
		$stagedQuestion = $this->setMessageAtStage($stage);
		$title = $this->output("Q%d Stage %d", count($stagedQuestion) + 1, $stage);
		$m = $this->saveMessage([
			"content" => str_replace([PHP_EOL, "\t"], "", $newline),
			"key" => Messages::KEY_TYPE_QUESTION,
			"page_id" => $this->getPageId(),
			"user_id" => $this->getUserId(),
			"stage" => $stage,
			"title" => $title,
			"name" => $this->getCurrentScene(),
			"id_linked_content_meta" => $idLinkedContentMeta
		]);
		if (!$m) {
			throw new Exception("Error saving question to database");
		} else {
			$this->setMessageValueAtStage($stage, $i, $title, $m["id"]);
			return $m;
		}
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
	* Set the list of staged ordered questions.
	*
	* @param 	int 	$stage
	* @return 	array 
	*/
	private function getMessageAtStage($stage, $type = "question") {

		$stagedMessages = ($type === Messages::KEY_TYPE_QUESTION) ? $this->stagedQuestions : $this->stagedResponses;
		return $stagedMessages["S" . $stage];
	}

	/**
	* Create new entry for question at stage.
	*
	* @param 	int 	$stage
	* @param 	array $stagedQuestion
	*/
	private function setMessageAtStage($stage, $type = "question") {

		$r = [];
		if ($type === Messages::KEY_TYPE_QUESTION) {
			$this->stagedQuestions["S" . $stage] = (!isset($this->stagedQuestions["S" . $stage])) ? [] : $this->stagedQuestions["S" . $stage];
			$r = $this->stagedQuestions["S" . $stage];
		} else {
			$this->stagedResponses["S" . $stage] = (!isset($this->stagedResponses["S" . $stage])) ? [] : $this->stagedResponses["S" . $stage];
			$r = $this->stagedResponses["S" . $stage];
		}
		return $r;
	}

	/**
	* Set the value for question at stage. Vaule has a convention "pos", "t", "id"
	*
	* @param 	int 	$stage
	* @param 	int 	$i
	* @param 	string 	$title
	* @param 	int 	$id
	*/
	private function setMessageValueAtStage($stage, $pos, $title, $id, $type = "question") {

		if ($type === Messages::KEY_TYPE_QUESTION) {
			$this->stagedQuestions["S" . $stage][] = ["pos" => $pos, "t" => $title, "id" => $id];
		} else {
			$this->stagedResponses["S" . $stage][] = ["pos" => $pos, "t" => $title, "id" => $id];
		}
	}

	/**
	* Set id_linked_content_meta of goto.
	*
	* @param 	int 	$pos 	position of goto
	* @param 	int|string 	$idLinkedContentMeta 	ID of question with label 	
	*/
	private function setGoToLinkedID($pos, $idLinkedContentMeta) {

		if (isset($this->gotos[$pos], $this->gotos[$pos]["id_linked_content_meta"])) {
			$this->gotos[$pos]["id_linked_content_meta"] = $idLinkedContentMeta;
		}
	}

	/**
	* Link the GoTo to it's corresponding label by the name.
	* NOTE: This must be executed after the whole file has been processed
	*
	* @param 	string 	$name
	* @return 	int 	$idLinkedContentMeta
	*/
	private function LinkGoTosAndLabels() {

		$labels = $this->getLabels();
		$gotos = $this->getGoTos();
		$c = count($gotos);
		foreach ($labels as $label) {
			for ($i = 0; $i < $c; $i++) {
				$goto = $gotos[$i];
				if ($goto["label"] === $label["label"] && $label["id"] !== 0) {
					$this->setGoToLinkedID($i, $label["id"]);
					$this->updateMessageGoto($goto["id"], $label["id"]);
				}
			}
		}
	}

	/**
	* Find messages in the database and update their id_linked_content_meta with the correct values
	* NOTE: This must only be executed after gotos have been assigned and linked
	*
	* @param 	int 	$id
	* @param 	int 	$idLinkedContentMeta
	*/
	private function updateMessageGoto($id, $idLinkedContentMeta) {

		$response = Messages::findOrFail($id);
		$response->goto = $idLinkedContentMeta;
		$response->save();
	}

	/**
	* Check if the current line is a label.
	*
	* @param 	string 	$line
	* @return 	int 	$r 	position of label
	*/
	private function isQuestionLabel(string $line) {

		$r = -1;
		$line = str_replace(["\t", " "], "", strtolower($line));
		$labels = $this->getLabels();
		$c = count($labels);
		for ($i = 0; $i < $c; $i++) {
			$l = $labels[$i];
			if ($line == str_replace(["\t", " "], "", strtolower($l["content"]))) {
				$r = $i;
				break;
			}
		}
		return $r;
	}

	/**
	* Assign a label with an ID.
	*
	* @param 	int 	$pos
	* @param 	int 	$id
	*/
	private function assignLabelId($pos, $id) {

		if (isset($this->labels[$pos], $this->labels[$pos]["id"])) {
			$this->labels[$pos]["id"] = $id;
		}
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
		if (!$page) {
			$page = Page::where("slug", "=", "/")->first();
			$this->pageId = (!$page) ? false : $page->id;
		}
	}

	/**
	* Set page slug.
	*
	* @param 	string 	$slug
	*/
	private function setPageSlug($slug) {

		$this->pageSlug = $slug;
	}

	/**
	* Get page slug.
	*
	* @return 	string 	$slug
	*/
	private function getPageSlug() {

		return $this->pageSlug;
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
	* Get list of scenes.
	*
	* @return 	array 	$scenes
	*/
	private function getScenes() {

		return $this->scenes;
	}

	/**
	* Set list of scenes.
	*
	* @param 	array 	$scenes
	*/
	private function setScenes($scenes) {

		$this->scenes = $scenes;
	}

	/**
	* Add a new scene and return new array
	*
	* @param 	string 	$scene
	* @return 	array 	$scenes
	*/
	private function addScene($scene) {

		$this->scenes[] = $scene;
		return $this->getScenes();
	}

	/**
	* Get list of labels.
	*
	* @return 	array 	$labels
	*/
	private function getLabels() {

		return $this->labels;
	}

	/**
	* Get singular label by name.
	*
	* @param 	string 	$name
	* @return 	array 	$label
	*/
	private function getLabel($name) {

		$r = [];
		foreach ($this->labels as $l) {
			if (strtolower($name) == strtolower($l["label"])) {
				$r = $l;
				break;
			}
		}
		return $r;
	}

	/**
	* Set list of labels.
	*
	* @param 	array 	$labels
	*/
	private function setLabels($labels) {

		$this->labels = $labels;
	}

	/**
	* Add a new label.
	*
	* @param 	string 	$label	
	* @param 	string 	$line
	*/
	private function addLabel(string $label, string $line) {

		$this->labels[] = [
			"id" => 0,
			"label" => str_replace([" ", "\t"], "", strtolower($label)),
			"content" => $line
		];
	}

	/**
	* Get list of gotos.
	*
	* @return 	array 	$gotos
	*/
	private function getGoTos() {

		return $this->gotos;
	}

	/**
	* Set list of gotos.
	*
	* @param 	array 	$gotos
	*/
	private function setGoTos($gotos) {

		$this->gotos = $gotos;
	}

	/**
	* Add a goto.
	*
	* @param 	int 	$id 	ID of response
	* @param 	string 	$label 
	* @param 	int|string 	$idLinkedContentMeta 	ID of question with label 	
	*/
	private function addGoTo($id, $label, $idLinkedContentMeta = 0) {

		$this->gotos[] = [
			"id" => $id,
			"label" => str_replace([" ", "\t"], "", strtolower($label)),
			"id_linked_content_meta" => $idLinkedContentMeta
		];
	}

	/**
	* Get the current scene.
	*
	* @return 	string 	$currentScene
	*/
	private function getCurrentScene() {

		return $this->currentScene;
	}

	/**
	* Set the current scene.
	*
	* @param 	string 	$currentScene
	*/
	private function setCurrentScene($scene) {

		$this->currentScene = $scene;
	}

	/**
	* Get the list of staged ordered questions.
	*
	* @return 	array 	$stagedQuestions
	*/
	private function getStagedQuestions() {

		return $this->stagedQuestions;
	}

	/**
	* Set the list of staged ordered questions.
	*
	* @param 	array 	$stagedQuestions
	*/
	private function setStagedQuestions($stagedQuestions) {

		$this->stagedQuestions = $stagedQuestions;
	}

	/**
	* Get the list of stageless unordered questions.
	*
	* @return 	array 	$stagelessQuestions
	*/
	private function getStagelessQuestions() {

		return $this->stagelessQuestions;
	}

	/**
	* Set the list of stageless unordered questions.
	*
	* @param 	array 	$stagelessQuestions
	*/
	private function setStagelessQuestions($stagelessQuestions) {

		$this->stagelessQuestions = $stagelessQuestions;
	}

	/**
	* Get the list of staged ordered Responses.
	*
	* @return 	array 	$stagelessResponses
	*/
	private function getStagedResponses() {

		return $this->stagedResponses;
	}

	/**
	* Set the list of staged ordered Responses.
	*
	* @param 	array 	$stagedResponses
	*/
	private function setStagedResponses($stagedResponses) {

		$this->stagedResponses = $stagedResponses;
	}

	/**
	* Get the list of stageless unordered Responses.
	*
	* @return 	array 	$stagelessResponses
	*/
	private function getStagelessResponses() {

		return $this->stagelessResponses;
	}

	/**
	* Set the list of stageless unordered Responses.
	*
	* @param 	array 	$stagelessResponses
	*/
	private function setStagelessResponses($stagelessResponses) {

		$this->stagelessResponses = $stagelessResponses;
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
