<?php

use App\User;
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
		$user_portchris = false;
		$role_admin = false;
		$isForced = $this->command->hasOption('force');
		Eloquent::unguard();

		if ($isForced) {
			$this->command->warn("Force mode ENGAGED!");
			Schema::dropIfExists('users');
			Schema::dropIfExists('roles');
			Schema::dropIfExists('pages');
			Schema::dropIfExists('content_metas');
		}

		// Clear out tables
		if (!Schema::hasTable('users')) {
			try {
				Schema::create('users', function ($table) {
					$table->increments('id');
					$table->string('firstname')->default("Joe");
					$table->string('lastname')->default("Bloggs");
					$table->string('email')->default("joe@bloggs.com");
					$table->string('username')->default("joebloggs");
					$table->integer('stage')->default(0);
					$table->float('lat')->default(0);
					$table->float('lng')->default(0);
					$table->boolean('enabled')->default(false);
					$table->longText('conversation')->nullable(true);
					$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
					$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
				});

				// Create all necessary users.
				$user_portchris = User::where('username', "chris@portchris.co.uk")->first();
				if (!$user_portchris) {
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
				}
				$this->command->info('Hello! User Chris reporting for duty sir!');
			} catch (Exception $e) {
				$this->command->error($e->getMessage());
			}
		} else {
			$user_portchris = User::where('username', "chris@portchris.co.uk")->first();
		}
		if (!Schema::hasTable('roles')) {
			Schema::create('roles', function ($table) {
				$table->increments('id');
				$table->string('name');
				$table->mediumText('description');
				$table->integer('security_level');
				$table->boolean('enabled');
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
			});

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
		}
		if (!Schema::hasTable('pages') && $user_portchris) {
			Schema::create('pages', function ($table) {
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
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
			});

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
		}
		if (!Schema::hasTable('users_roles')) {
			Schema::create('users_roles', function ($table) {
				$table->increments('id');
				$table->integer('user_id');
				$table->integer('role_id');
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
			});
		}
		if (!Schema::hasTable('content_metas') && $user_portchris) {
			Schema::create('content_metas', function ($table) {
				$table->increments('id');
				$table->string('name');
				$table->integer('id_linked_content_meta');
				$table->string('title');
				$table->string('key');
				$table->string('goto');
				$table->integer('stage');
				$table->longText('content');
				$table->integer('user_id');
				$table->integer('page_id');
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
			});
			$this->command->info('Created the messages table, Let the stories begin!');
		}
		if ($user_portchris && $role_admin) {
			$user_portchris->roles()->attach($role_admin->id);
			$this->command->info("Portchris is now an admin... Hope you don't mind.");
		}
	}
}
