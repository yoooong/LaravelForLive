<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->tinyInteger('age')->unsigned()->default(0)->after('nickname');//年龄
            $table->tinyInteger('sexual')->unsigned()->default(0)->after('sex');//性取向
            $table->tinyInteger('constell')->unsigned()->default(0)->after('sexual');//星座
            $table->tinyInteger('marital')->unsigned()->default(0)->after('constell');//婚姻状态
            $table->string('signature')->after('city');//个性签名
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
}
