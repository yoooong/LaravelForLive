<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTargetUserIdToAppointmentUserScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointment_user_score', function (Blueprint $table) {
            $table->integer('target_user_id')->unsigned()->comment('被评论用户id')->after('user_id')->index();
            $table->string('avatar')->after('user_id')->nullable();//头像
            $table->string('nickname', 20)->after('user_id');//昵称

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointment_user_score', function (Blueprint $table) {
            //
        });
    }
}
