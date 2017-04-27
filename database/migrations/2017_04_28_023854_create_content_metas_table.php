<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('content_metas');
        Schema::create('content_metas', function (Blueprint $table) {
            
            // Primary key
            $table->increments('id');

            // Our one-to-many relationship foreign key
            $table->integer('user_id');

            // Our one-to-many relationship foreign key
            $table->integer('page_id');

            // So the computer knows how to respond i.e question, answer 
            $table->integer('id_linked_content_meta');

            // Name of the meta, this table could also serve as a content block on a page
            $table->string('name');

            // Title of section
            $table->string('title');

            // Constant, will categorise this meta
            $table->string('key');

            // Checkpoint, so we know when this question should be asked for example
            $table->string('stage');

            // The body of this meta, can be HTML
            $table->text('content');

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
        Schema::dropIfExists('content_metas');
    }
}
