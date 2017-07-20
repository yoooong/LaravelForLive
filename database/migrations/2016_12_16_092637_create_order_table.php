<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trade_id')->unsigned();//订单ID
            $table->integer('user_id')->unsigned();//用户ID
            $table->decimal('amount', 10, 2)->unsigned()->default(0);//总价
            $table->tinyInteger('status')->unsigned()->default(0);//状态：0-未支付，1-支付成功，2-超时
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
        Schema::dropIfExists('order');
    }
}
