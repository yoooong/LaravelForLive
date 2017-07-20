<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_exchange', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();                 //用户id
            $table->integer('rmb')->unsigned()->default(0);         //人民币
            $table->tinyInteger('state')->unsigned()->default(0);   //状态，0未领取，1已领取
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
        Schema::dropIfExists('user_exchange');
    }
}
