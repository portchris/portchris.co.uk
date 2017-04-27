<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {

            // Primary key
            $table->increments('id');

            // Name of the role
            $table->string('name');

            // Describe what this role is allowed to do to refresh my memory
            $table->string('description');

            // Their clearance level
            $table->integer('security_level');

            // Soft delete
            $table->boolean('enabled');

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
        Schema::dropIfExists('roles');
    }
}
