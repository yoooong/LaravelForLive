<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHongbaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_hongbao', function (Blueprint $table) {
            $table->increments('id')->comment('用户红包');
            $table->integer('user_id')->unsigned()->comment('用户');
            $table->integer('to_user_id')->unsigned()->comment('对方用户');
            $table->decimal('value', 8, 2)->unsigned()->comment('红包值');
            $table->string('code', 6)->comment('口令');
            $table->integer('create_time')->unsigned()->comment('红包生成时间');
            $table->integer('fd')->unsigned()->comment('连接句柄');
            $table->tinyInteger('status')->unsigned()->comment('状态：0未领取，1已领取');
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
        //
    }
}
