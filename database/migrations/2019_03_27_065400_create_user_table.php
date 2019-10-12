<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->comment = '用户';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('mobile', 11)->comment('手机号');
            $table->string('nickname', 50)->comment('昵称');
            $table->string('avatar')->comment('头像');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性别[1:男,2:女,0:未知]');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态[1:正常,2:禁用]');

            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->comment('更新时间');

            $table->unique('mobile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
