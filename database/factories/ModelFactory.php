<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
	static $password;

	return [
		'name' => $faker->name,
		'email' => $faker->unique()->safeEmail,
		'password' => $password ?: $password = bcrypt('secret'),
		'remember_token' => str_random(10),
	];
});

$factory->define(App\Book::class, function (Faker\Generator $faker) {

	return [
		'author' => $faker->name,
		'description' => $faker->text,
	];
});

// Create factory for messages
$factory->define(App\ContentMeta::class, function (Faker\Generator $faker) {

	return [
		'name' => 'Text based adventure with Joe Blogs',
		'id_linked_content_meta' => 1, // This is how the site answers questions
		'title' => "Joe Blogs - Stage 1",
		'key' => "answer",
		'stage' => 1,
		'content'=> "My name is Joe blogs, I want to know more about you",
		'user_id' => 4, // Portchris
		'page_id' => 4 // Homepage
	];
});