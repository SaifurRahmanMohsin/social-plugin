<?php namespace Mohsin\Social\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSettingsTable extends Migration
{

    public function up()
    {
        Schema::create('mohsin_social_settings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mohsin_social_settings');
    }

}
