<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('姓名');
            $table->string('sex')->comment('性别');
            $table->string('mobile')->nullable()->comment('手机号');
            $table->integer('qz5z_uid')->comment('五中 UID');
            $table->string('qz5z_grade')->comment('五中届数');
            $table->string('qz5z_class')->comment('五中班级');
            $table->integer('qz5z_number')->nullable()->comment('五中座号');
            $table->string('token')->nullable()->comment('OAuth token');
            $table->string('refresh_token')->nullable()->comment('OAuth refresh token');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
