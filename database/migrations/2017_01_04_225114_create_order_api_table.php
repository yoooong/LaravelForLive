<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_api', function (Blueprint $table) {
            $table->integer('order_id')->unsigned();
            $table->string('trade_id');
            $table->tinyInteger('type')->unsigned();//方式：1-微信支付，2-支付宝支付，3-苹果支付
            $table->text('response_body');
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
        Schema::dropIfExists('order_api');
    }
}
