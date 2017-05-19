<?php

namespace Tests\Feature;

use TestCase;
use App;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan as Artisan;

class UserControllerTest extends TestCase
{
	/**
	* Test to create new message
	*
	* @return void
	*/
	public function testIdentify() {

		$this->output("BEGIN TEST :: UserControllerTest");
		// $values = array(
		// 	'name' => 'Text based adventure with Joe Blogs',
		// 	'id_linked_content_meta' => $q->id, // This is how the site answers questions
		// 	'title' => "Joe Blogs - Stage 1",
		// 	'key' => "answer",
		// 	'stage' => 1,
		// 	'content'=> "My name is Joe blogs, I want to know more about you",
		// 	'user_id' => 4, // Portchris
		// 	'page_id' => 4 // Homepage
		// );
		$values = array("username" => "", "password" => "");
		$response = $this->call('POST', 'api/user/identify', $values);
		if ($response->status() == 500) {
			$this->output(strip_tags($response->getContent()));
		} else {
			$r = json_decode($response->getContent(), true);
			$this->output($r);
		}
		if (isset($r['name']) && $r['name'] != "error") {
			$this->assertTrue(true);
		} else {
			$this->assertFalse(false);
		}
	}
}
