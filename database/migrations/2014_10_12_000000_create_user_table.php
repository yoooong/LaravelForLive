<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unionid', 28);
            $table->string('openid', 28);
            $table->string('avatar')->nullable();//头像
            $table->string('nickname', 20);//昵称
            $table->tinyInteger('sex')->unsigned()->default(0);//性别
            $table->string('country', 50);//国家
            $table->string('province', 50);//省份
            $table->string('city', 50);//城市
            $table->string('password');
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
        Schema::drop('user');
    }
}
