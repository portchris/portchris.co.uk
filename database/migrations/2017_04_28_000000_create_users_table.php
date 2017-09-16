<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {

            // Primary key
            $table->increments('id');

            // Full name
            $table->string('name');

            // First name for when we get friendly
            $table->string('firstname');

            // Last name if they decide to be unfriendly
            $table->string('lastname');

            // Reel in that data
            $table->string('email')->unique();

            // Make it the same as the email for now
            $table->string('username')->unique();

            // Touch of security
            $table->string('password');

            // Checkpoint, if they create an account their progress is stored here
            $table->integer('stage');

            // So I know where in the world they are
            $table->string('lat');
            $table->string('lng');

            // JSON array, if the user creates an account their conversation is stored here
            $table->text('conversation');

            // Soft deleting tactic
            $table->boolean('enabled');

            // updated_at, created_at timestamp
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
