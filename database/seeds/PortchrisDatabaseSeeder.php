<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortchrisDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		Eloquent::unguard();

		// Clear out tables
		Schema::dropIfExists('users');
		if (Schema::hasTable('users')) {
			DB::table('users')->delete();
		} else {
			Schema::create('users', function ($table) {
				$table->increments('id');
				$table->string('firstname');
				$table->string('lastname');
				$table->string('email');
				$table->string('username');
				$table->integer('stage');
				$table->float('lat');
				$table->float('lng');
				$table->boolean('enabled');
				$table->longText('conversation');
			});
		}
		Schema::dropIfExists('roles');
		if (Schema::hasTable('roles')) {
			DB::table('roles')->delete();
		} else {
			Schema::create('roles', function ($table) {
				$table->increments('id');
				$table->string('name');
				$table->mediumText('description');
				$table->integer('security_level');
				$table->boolean('enabled');
			});
		}
		Schema::dropIfExists('pages');
		if (Schema::hasTable('pages')) {
			DB::table('pages')->delete();
		} else {
			Schema::create('users', function ($table) {
				$table->increments('id');
				$table->string('name');
				$table->string('title');
				$table->longText('content');
				$table->string('slug');
				$table->mediumText('meta_title');
				$table->mediumText('meta_description');
				$table->mediumText('meta_image_path');
				$table->boolean('enabled');
				$table->integer('user_id');
			});
		}
		Schema::dropIfExists('content_metas');
		if (Schema::hasTable('content_metas')) {
			DB::table('content_metas')->delete();
		} else {
			Schema::create('content_metas', function ($table) {
				$table->increments('id');
				$table->string('name');
				$table->integer('id_linked_content_meta');
				$table->string('title');
				$table->string('key');
				$table->integer('stage');
				$table->longText('content');
				$table->integer('user_id');
				$table->integer('page_id');
			});
		}

		// Create all necessary users.
		$user_portchris = App\User::create(array(
			'name' => 'Chris Rogers',
			'firstname' => 'Chris',
			'lastname' => 'Rogers',
			'email' => 'chris@portchris.co.uk',
			'username' => 'chris@portchris.co.uk',
			'password' => Hash::make('$1Flapjack'),
			'stage' => 1,
			'lat' => '0.000000',
			'lng' => '0.000000',
			'enabled' => true
		));
		$this->command->info('Hello! User Chris reporting for duty sir!');

		// Create all main roles
		$role_admin = App\Role::create(array(
			'name' => 'Administrator',
			'description' => 'Admins have the most power. Only God surpasses them. Basically only Chris is an admin so he can log in.',
			'security_level' => 1,
			'enabled' => true
		));
		$role_user = App\Role::create(array(
			'name' => 'User',
			'description' => 'Users are people who have agree to create an account after speaking with the portchris engine. Their conversation and progress is saved for the future reference.',
			'security_level' => 10,
			'enabled' => true
		));
		$role_guest = App\Role::create(array(
			'name' => 'Guest',
			'description' => 'Most basic role. They are users that do not create an account. Their information is not saved. All visitors are of this role unless they login.',
			'security_level' => 100,
			'enabled' => true
		));
		$this->command->info('Major roles created!');

		// Create all main pages.
		$meta_desc = 'Developer skilled in the following languages, programs, tools, frameworks and platforms: HTML, CSS, JS, PHP, XML, JSON, C#, NGINX, Apache, Linux, Photoshop, Unity3D, Maya 3D, Wordpress, Magento, Laravel, AngularJS and other lesser known.';
		$page_home = App\Page::create(array(
			'name' => 'Homepage',
			'title' => 'Welcome, my name is Chris Rogers - digital application developer. What can I do for you?',
			'content' => '',
			'slug' => '/',
			'meta_title' => 'Chris Rogers, Web Application Developer.',
			'meta_description' => $meta_desc,
			'meta_image_path' => '',
			'enabled' => true,
			'user_id' => $user_portchris->id
		));
		$page_about = App\Page::create(array(
			'name' => 'About me',
			'title' => 'Lets talk some more, maybe in person sometime?',
			'content' => '',
			'slug' => 'about',
			'meta_title' => 'About Chris Rogers, Web Application Developer.',
			'meta_description' => $meta_desc,
			'meta_image_path' => '',
			'enabled' => true,
			'user_id' => $user_portchris->id
		));
		$page_contact = App\Page::create(array(
			'name' => 'Contact me',
			'title' => 'Lets talk some more, maybe in person sometime?',
			'content' => '',
			'slug' => 'contact',
			'meta_title' => 'Contact Chris Rogers, Web Application Developer.',
			'meta_description' => $meta_desc,
			'meta_image_path' => '',
			'enabled' => true,
			'user_id' => $user_portchris->id
		));
		$this->command->info('Main pages are now ready!');

		// Create dummy content metas.
		$meta_q = App\ContentMeta::create(array(
			'name' => 'Q1 Stage 1',
			'id_linked_content_meta' => 0,
			'title' => "Let's get started",
			'key' => "question",
			'stage' => 1,
			'content' => "What's your name?",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$meta_q2 = App\ContentMeta::create(array(
			'name' => 'Q1 Stage 0',
			'id_linked_content_meta' => 0,
			'title' => "Let's get started",
			'key' => "question",
			'stage' => 0,
			'content' => "Is there anything else I can help you with?",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$meta_r = App\ContentMeta::create(array(
			'name' => 'R1 Stage 1',
			'id_linked_content_meta' => $meta_q->id, // This is how the site answers questions
			'title' => "Let's get started",
			'key' => "response",
			'stage' => 1,
			'content' => "I'd like to know more about you",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$meta_r2 = App\ContentMeta::create(array(
			'name' => 'R2 Stage 1',
			'id_linked_content_meta' => $meta_q->id, // This is how the site answers questions
			'title' => "Let's get started",
			'key' => "response",
			'stage' => 1,
			'content' => "I'm not saying",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$meta_a = App\ContentMeta::create(array(
			'name' => 'A1 Stage 1',
			'id_linked_content_meta' => $meta_r->id, // This is how the site answers questions
			'title' => "Let's get started",
			'key' => "answer",
			'stage' => 1,
			'content' => "Great! My name is Chris! But you should already know that :p",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$meta_a2 = App\ContentMeta::create(array(
			'name' => 'A1 Stage 0',
			'id_linked_content_meta' => $meta_r2->id, // This is how the site answers questions
			'title' => "Let's get started",
			'key' => "answer",
			'stage' => 0,
			'content' => "Gotcha, well then there's no more reason to talk I suppose.",
			'user_id' => $user_portchris->id,
			'page_id' => $page_home->id
		));
		$this->command->info('Inserted some dummy questions and answers for ya!');

		$user_portchris->roles()->attach($role_admin->id);
		$this->command->info("Portchris is now an admin... Hope you don't mind.");
	}
}
