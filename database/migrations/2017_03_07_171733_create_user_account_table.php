<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_account', function (Blueprint $table) {
            $table->increments('id')->comment('用户账户');
            $table->integer('user_id')->unsigned()->comment('下单用户');
            $table->integer('business')->unsigned()->comment('入账业务');
            $table->integer('business_id')->unsigned()->comment('入账业务的id');
            $table->tinyInteger('type')->unsigned()->comment('入账类型：1小票，2钻石');
            $table->decimal('value', 10, 2)->comment('入账金额');
            $table->tinyInteger('status')->unsigned()->comment('状态：0待确认，1已确认');
            $table->string('ukey')->comment('并发键');
            $table->unique('ukey');
            $table->string('remark')->comment('备注');
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
