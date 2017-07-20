<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_receipt', function (Blueprint $table) {
            $table->integer('user_id')->unsigned(); //用户id
            $table->primary('user_id');
            $table->integer('receipt')->unsigned()->default(0); //小票
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
        Schema::dropIfExists('user_receipt');
    }
}
