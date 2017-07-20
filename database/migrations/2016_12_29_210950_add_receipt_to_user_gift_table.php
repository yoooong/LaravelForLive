<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceiptToUserGiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_gift', function (Blueprint $table) {
            $table->integer('receipt')->unsigned()->default(0)->after('gift_id');//收到礼物时的小票数
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_gift', function (Blueprint $table) {
            //
        });
    }
}
