<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_gift', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();//用户ID
            $table->integer('gift_id')->unsigned();//礼物ID
            $table->tinyInteger('state');
            $table->integer('from_uid')->unsigned();//礼物ID
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
        Schema::dropIfExists('user_gift');
    }
}
