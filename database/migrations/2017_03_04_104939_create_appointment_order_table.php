<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_order', function (Blueprint $table) {
            $table->increments('id')->comment('预约订单id');
            $table->integer('order_id')->unsigned()->comment('支付订单id');
            $table->integer('user_id')->unsigned()->comment('下单用户');
            $table->integer('target_user_id')->unsigned()->comment('被预约用户');
            $table->dateTime('order_time')->comment('下单时间');
            $table->integer('product_id')->unsigned()->comment('产品id');
            $table->integer('num')->unsigned()->comment('数量');
            $table->tinyInteger('status')->unsigned()->comment('确认状态：0待确认，1已确认，2已拒绝');
            $table->tinyInteger('payed')->unsigned()->comment('支付状态：0未支付，1已支付，2已退款');
            $table->text('remark')->comment('备注');
            $table->tinyInteger('score')->default(0)->unsigned()->nullable()->comment('评分');
            $table->text('comment')->nullable()->comment('评论');
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
