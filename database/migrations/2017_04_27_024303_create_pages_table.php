<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pages');
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
            $table->string('meta_title')->default("");
            $table->string('meta_description')->default("");
            $table->string('meta_image_path')->default("");

            // Soft delete
            $table->boolean('enabled')->default(true);

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
