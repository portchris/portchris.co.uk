<?php

namespace Tests\Feature;

use TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan as Artisan;

class MessageStreamControllerTest extends TestCase
{

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
	* Test if the show functionality returns a message.
	*
	* @return void
	*/
	public function testCreate() {

		$response = $this->call('GET', 'api/message/create');
		$this->assertEquals(405, $response->status());
	}

	/**
	* Test to create new message
	*
	* @return void
	*/
	public function testStore() {

		// Session::start();
		$values = array(
			'name' => 'Text based adventure with Joe Blogs',
			'id_linked_content_meta' => 1, // This is how the site answers questions
			'title' => "Joe Blogs - Stage 1",
			'key' => "answer",
			'stage' => 1,
			'content'=> "My name is Joe blogs, I want to know more about you",
			'user_id' => 4, // Portchris
			'page_id' => 4 // Homepage
		);
		$response = $this->call('POST', 'api/message', $values);
		$this->seeInDatabase('content_metas', ['title' => 'Joe Blogs - Stage 1']);
		$this->assertResponseOk();
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
		Artisan::output(get_class($response));
		$this->assertEquals(405, $response->status());
	}

}
