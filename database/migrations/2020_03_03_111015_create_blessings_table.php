<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlessingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blessings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('发表者 ID');
            $table->text('title')->nullable()->comment('祝福标题');
            $table->text('content')->comment('祝福内容');
            $table->string('color')->nullable()->comment('祝福颜色');
            $table->text('ip')->nullable()->comment('IP');
            $table->text('ua')->nullable()->comment('UA');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blessings');
    }
}
