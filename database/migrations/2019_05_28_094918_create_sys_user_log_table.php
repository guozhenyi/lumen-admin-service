<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_user_log', function (Blueprint $table) {
            $table->comment = '系统用户日志';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->unsignedInteger('user_id')->comment('系统用户ID');

            $table->string('describe')->comment('描述');

            $table->timestamp('created_at')->useCurrent();

            $table->string('ipv4', 40)->default('')->comment('IP地址');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_user_log');
    }
}
