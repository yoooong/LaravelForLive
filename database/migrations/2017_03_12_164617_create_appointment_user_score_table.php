<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentUserScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_user_score', function (Blueprint $table) {
            $table->increments('id')->comment('用户订单评分表');
            $table->integer('appointment_order_id')->unsigned()->comment('订单id');
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->tinyInteger('score')->default(0)->unsigned()->comment('评分');
            $table->text('comment')->comment('评论');
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
