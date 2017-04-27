<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersRolesTable extends Migration
{
    /**
     * Run the migrations. Crucial link between the users and the roles table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_roles', function (Blueprint $table) {

            // Primary key, auto-increments
            $table->increments('id');

            // Linked role
            $table->integer('role_id');

            // Linked user
            $table->integer('user_id');

            // Created and updated timestamps
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
        Schema::dropIfExists('users_roles');
    }
}
