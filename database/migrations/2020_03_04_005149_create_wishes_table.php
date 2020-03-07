<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWishesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('发布者 ID')->index();
            $table->text('qq')->comment('发布者 QQ');
            $table->integer('status')->comment('状态。100: 没人接单 200：有人接单 300：已完成');
            $table->integer('type')->comment('分类。1: 实物类 2：其他');
            $table->text('content')->comment('愿望内容');
            $table->json('file_json')->nullable()->comment('附件 JSON');
            $table->boolean('is_graduate')->comment('是否毕业生发布');
            $table->integer('assigned_uid')->nullable()->comment('接单者 ID')->index();
            $table->timestamp('assigned_at')->nullable()->comment('接单时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->text('ip')->nullable()->comment('IP');
            $table->text('ua')->nullable()->comment('UA');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'is_graduate', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wishes');
    }
}
