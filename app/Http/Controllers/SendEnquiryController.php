<?php

/**
 * Wufoo enquiry submission
 *
 * @author 	Chris Rogers
 * @since 	1.0.0 <2017-06-19>
 * @see 		https://wufoo.github.io/docs/#submit-entry
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ContentMeta as Messages;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SendEnquiryController extends Controller
{
	/**
	 * Wufoo API key
	 * @var 	string
	 */
	private $key;

	/**
	 * Wufoo form uri
	 * @var 	string
	 */
	private $uri;

	public function __construct()
	{

		$this->key = "SLRA-2E7T-DREV-7U6L";
		$this->uri = "https://portchris.wufoo.com/api/v3/forms/zkm8dmp0003akf/entries.json";
	}

	/**
	 * Send enquiry to Wufoo submit using V3 API. Return message
	 * __invoke automatically called since it is the only route defined in Laravel
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function __invoke(Request $request)
	{

		$code = 200;
		$r = [];
		$data = $request->json()->all();
		$fields = $this->serialize($data);
		try {
			$request = $this->sendEnquiry($data);
			if ($request->getBody()) {
				$r = [
					"content" => __("Success, your enquiry was sent"),
					"key" => Messages::KEY_TYPE_ANSWER,
					"title" => Messages::KEY_TYPE_ANSWER,
					"name" => Messages::KEY_TYPE_ANSWER,
					"user_id" => $data["user_id"] ?? 0,
					"page_id" => $data["page_id"] ?? 0,
					"id_linked_content_meta" => $data["id_linked_content_meta"] ?? 0,
					"stage" => $data["stage"] ?? 0
				];
			} else {
				$r = [
					"content" => __("There was an error sending your enquiry, response returned NULL"),
					"key" => Messages::KEY_TYPE_ERROR,
					"title" => Messages::KEY_TYPE_ERROR,
					"name" => Messages::KEY_TYPE_ERROR,
					"user_id" => $data["user_id"] ?? 0,
					"page_id" => $data["page_id"] ?? 0,
					"id_linked_content_meta" => $data["id_linked_content_meta"] ?? 0,
					"stage" => $data["stage"] ?? 0
				];
			}
		} catch (GuzzleHttp\Exception\GuzzleException $e) {
			$code = ($e->getCode() > 0) ? $e->getCode() : 500;
			$r = [
				"content" => sprintf(__("There was an error sending the enquiry: "), $e->getMessage()),
				"key" => Messages::KEY_TYPE_ERROR,
				"title" => Messages::KEY_TYPE_ERROR,
				"name" => Messages::KEY_TYPE_ERROR,
				"user_id" => $data["user_id"] ?? 0,
				"page_id" => $data["page_id"] ?? 0,
				"id_linked_content_meta" => $data["id_linked_content_meta"] ?? 0,
				"stage" => $data["stage"] ?? 0
			];
		}
		return response()->json(Messages::create($r), $code);
	}

	/**
	 * cURL POST request specific to Wufoo Using Guzzle
	 *
	 * @param 	string 	$data
	 */
	private function sendEnquiry(array $data)
	{

		$client = new Client(); //GuzzleHttp\Client
		$result = $client->post($this->uri, [
			'form_params' => $data,
			'auth' => [$this->key, 'pass'],
			'headers' => ['Content-type' => 'application/x-www-form-urlencoded']
		]);
		return $result;
	}

	/**
	 * Url-ify the data for the POST
	 * @param 	array 	$data
	 * @return 	string 	
	 */
	private function serialize($fields)
	{

		return http_build_query($fields);
	}
}
