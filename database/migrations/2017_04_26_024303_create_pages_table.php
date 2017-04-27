<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {

            // Primary key, auto-increments
            $table->increments('id');

            // Name of this page, admin use.
            $table->string('name');

            // Title of this page
            $table->string('title');

            // URL
            $table->string('slug');

            // Main body will use HTML
            $table->text('content');

            // Page meta (Google)
            $table->string('meta_title');
            $table->string('meta_description');
            $table->string('meta_image_path');

            // Soft delete
            $table->boolean('enabled');

            // Created and updated times
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
        Schema::dropIfExists('pages');
    }
}
