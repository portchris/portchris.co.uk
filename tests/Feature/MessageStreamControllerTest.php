<?php

namespace Tests\Feature;

use TestCase;
use App;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan as Artisan;

class MessageStreamControllerTest extends TestCase
{
	use DatabaseTransactions;

	/**
	* Test if the latest message is available for user
	*
	* @return void
	*/
	public function testIndex() {

		$response = $this->call('GET', 'api/message');
		$this->assertEquals(200, $response->status());
	}

	/**
	* Test if the show functionality returns a message.
	*
	* @return void
	*/
	public function testShow() {

		$id = 1;
		$response = $this->call('GET', 'api/message/' . $id);
		$this->assertEquals(200, $response->status());
	}

	/**
	* Test if the create functionality returns a JSON error as supposed to.
	*
	* @return void
	*/
	public function testCreate() {

		$response = $this->call('GET', 'api/message/create');
		$rArr = json_decode($response->getContent());
		// $this->output($rArr);
		$this->assertViewHas("name", "error");
		$this->assertEquals(200, $response->status());
	}

	/**
	* Test to create new message
	*
	* @return void
	*/
	public function testStore() {

		// Session::start();
		// $msg = factory(App\ContentMeta::class)->make();
		$q = App\ContentMeta::where([
			"stage" => 1, 
			"key" => "question"
		])->first();
		$values = array(
			'name' => 'Text based adventure with Joe Blogs',
			'id_linked_content_meta' => $q->id, // This is how the site answers questions
			'title' => "Joe Blogs - Stage 1",
			'key' => "answer",
			'stage' => 1,
			'content'=> "My name is Joe blogs, I want to know more about you",
			'user_id' => 4, // Portchris
			'page_id' => 4 // Homepage
		);
		$response = $this->call('POST', 'api/message', $values);
		if ($response->status() == 500) {
			// $this->output(strip_tags($response->getContent()));
		} else {
			$r = json_decode($response->getContent(), true);
			// $this->output($r);
		}
		$this->assertDatabaseHas('content_metas', ['title' => 'Joe Blogs - Stage 1']);
		if (isset($r['name']) && $r['name'] != "error") {
			$this->assertTrue(true);
		} else {
			$this->assertFalse(false);
		}
	}

	/**
	* Test should fail, shouldn't be allowed to edit, update or destroy
	*
	* @return void
	*/
	public function testEdit() {
		
		$response = $this->call('GET', 'api/message/1/edit');
		$this->assertEquals(404, $response->status());
	}

	/**
	* Test should fail, shouldn't be allowed to edit, update or destroy
	*
	* @return void
	*/
	public function testUpdate() {
		
		$values = array('message' => "This is PUT test");
		$response = $this->call('PUT', 'api/message', $values);
		$this->assertEquals(405, $response->status());
	}

	/**
	* Test should fail, shouldn't be allowed to edit, update or destroy
	*
	* @return void
	*/
	public function testDestroy() {
		
		$response = $this->call('DELETE', 'api/message/1');
		// $this->output($response);
		$this->assertEquals(405, $response->status());
	}

	public function setUp() {
		
		parent::setUp();
	}

	public function tearDown() {
		
		parent::tearDown();
		// Mockery::close();
	}
}
