<?php namespace Mohsin\Social\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSocialsTable extends Migration
{

    public function up()
    {
        Schema::create('mohsin_social_socials', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('facebook')->nullable();
            $table->string('google')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('microsoft')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mohsin_social_socials');
    }

}
