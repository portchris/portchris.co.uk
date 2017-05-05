<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
	/**
	* A basic functional test example.
	*
	* @return void
	*/
	public function testBasicExample() {
		$response = $this->call('GET', '/');
		// $response->see('Loading');
		$this->assertEquals(200, $response->status());
	}
}
